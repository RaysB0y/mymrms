<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inspector extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        require_role('INSPECTOR');
        $this->load->model('Rfi_model');
        $this->load->model('Material_model');
    }

    public function index()
    {
        $inspector_id = $this->session->userdata('user_id');

        $data['title'] = 'RFI Inspection';
        $data['rfis'] = $this->Rfi_model->get_by_inspector($inspector_id);

        $this->load->view('inspector/index', $data);
    }

    public function detail($id)
    {
        $inspector_id = $this->session->userdata('user_id');
        $rfi = $this->Rfi_model->get_by_id($id);

        if (!$rfi || $rfi->inspector_id != $inspector_id) {
            show_404();
        }

        $data['title'] = 'RFI Detail';
        $data['rfi'] = $rfi;
        $data['items'] = $this->Rfi_model->get_items($id);
        $data['approvals'] = $this->Rfi_model->get_approvals($id);

        $this->load->view('inspector/detail', $data);
    }

    public function check_item($item_id)
    {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $status = $this->input->post('status', TRUE);
        $remark = trim($this->input->post('remark', TRUE));

        if (!in_array($status, array('READY', 'NOT_READY'), TRUE)) {
            $this->session->set_flashdata('error', 'Status pemeriksaan tidak valid.');
            redirect('inspector');
            return;
        }

        $item = $this->Rfi_model->get_item_by_id($item_id);

        if (!$item) {
            show_404();
        }

        $rfi = $this->Rfi_model->get_by_id($item->rfi_id);
        $inspector_id = $this->session->userdata('user_id');

        if (!$rfi || $rfi->inspector_id != $inspector_id) {
            show_error('Anda tidak memiliki akses ke RFI ini.', 403);
        }

        if ($rfi->status !== 'SUBMITTED') {
            $this->session->set_flashdata(
                'error',
                'RFI ini tidak sedang menunggu pemeriksaan.'
            );
            redirect('inspector/detail/' . $rfi->id);
            return;
        }

        $material = $this->Material_model->get_active_by_id($item->material_id);

        if (!$material) {
            $this->session->set_flashdata(
                'error',
                'Material tidak ditemukan atau sudah tidak aktif.'
            );
            redirect('inspector/detail/' . $rfi->id);
            return;
        }

        if ($status === 'NOT_READY' && $remark === '') {
            $this->session->set_flashdata(
                'error',
                'Remark wajib diisi jika material NOT READY.'
            );
            redirect('inspector/detail/' . $rfi->id);
            return;
        }

        $approved_qty = 0;

        if ($status === 'READY') {
            $approved_qty = min(
                (float) $item->requested_qty,
                (float) $material->quantity
            );

            if ($approved_qty <= 0) {
                $this->session->set_flashdata(
                    'error',
                    'Stock material tidak tersedia. Gunakan NOT READY.'
                );
                redirect('inspector/detail/' . $rfi->id);
                return;
            }
        }

        $this->Rfi_model->update_item($item_id, array(
            'approved_qty' => $approved_qty,
            'status' => $status,
            'remark' => $status === 'NOT_READY' ? $remark : NULL,
            'checked_by' => $inspector_id,
            'checked_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));

        $items = $this->Rfi_model->get_items($rfi->id);
        $all_checked = TRUE;
        $has_not_ready = FALSE;

        foreach ($items as $current_item) {
            if ($current_item->status === 'PENDING') {
                $all_checked = FALSE;
            }

            if ($current_item->status === 'NOT_READY') {
                $has_not_ready = TRUE;
            }
        }

        if (!$all_checked) {
            $this->session->set_flashdata(
                'success',
                'Pemeriksaan item berhasil disimpan.'
            );
            redirect('inspector/detail/' . $rfi->id);
            return;
        }

        if ($has_not_ready) {
            $this->db->trans_start();

            $this->Rfi_model->update($rfi->id, array(
                'status' => 'REVISION_REQUIRED',
                'rejected_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ));

            $this->Rfi_model->add_approval(array(
                'rfi_id' => $rfi->id,
                'revision' => $rfi->revision,
                'inspector_id' => $inspector_id,
                'decision' => 'REVISION_REQUIRED',
                'remark' => $remark
            ));

            $this->db->trans_complete();

            if (!$this->db->trans_status()) {
                $this->session->set_flashdata(
                    'error',
                    'Gagal menyimpan hasil pemeriksaan.'
                );
            } else {
                $this->session->set_flashdata(
                    'success',
                    'RFI memerlukan revisi. RFI dikembalikan ke DOCONT.'
                );
            }

            redirect('inspector/detail/' . $rfi->id);
            return;
        }

        $this->_approve_rfi($rfi->id, $inspector_id);
    }

    private function _approve_rfi($rfi_id, $inspector_id)
    {
        $this->db->trans_begin();

        $rfi = $this->Rfi_model->get_by_id($rfi_id);

        if (!$rfi || $rfi->status !== 'SUBMITTED') {
            $this->db->trans_rollback();
            $this->session->set_flashdata(
                'error',
                'RFI sudah tidak dapat diproses.'
            );
            redirect('inspector');
            return;
        }

        $items = $this->Rfi_model->get_items($rfi_id);

        if (empty($items)) {
            $this->db->trans_rollback();
            $this->session->set_flashdata(
                'error',
                'RFI tidak memiliki item material.'
            );
            redirect('inspector/detail/' . $rfi_id);
            return;
        }

        foreach ($items as $item) {
            if ($item->status !== 'READY') {
                $this->db->trans_rollback();
                $this->session->set_flashdata(
                    'error',
                    'Semua item harus READY sebelum RFI dapat di-approve.'
                );
                redirect('inspector/detail/' . $rfi_id);
                return;
            }

            $material = $this->db
                ->where('id', $item->material_id)
                ->where('is_active', TRUE)
                ->get('materials')
                ->row();

            if (!$material) {
                $this->db->trans_rollback();
                $this->session->set_flashdata(
                    'error',
                    'Material tidak ditemukan.'
                );
                redirect('inspector/detail/' . $rfi_id);
                return;
            }

            $approved_qty = (float) $item->approved_qty;
            $stock_before = (float) $material->quantity;

            if ($approved_qty > $stock_before) {
                $this->db->trans_rollback();
                $this->session->set_flashdata(
                    'error',
                    'Stock material berubah. Silakan lakukan pemeriksaan ulang.'
                );
                redirect('inspector/detail/' . $rfi_id);
                return;
            }

            $stock_after = $stock_before - $approved_qty;

            $this->db
                ->where('id', $material->id)
                ->update('materials', array(
                    'quantity' => $stock_after,
                    'updated_at' => date('Y-m-d H:i:s')
                ));

            $this->Material_model->add_transaction(array(
                'material_id' => $material->id,
                'transaction_type' => 'OUT',
                'quantity' => -$approved_qty,
                'quantity_before' => $stock_before,
                'quantity_after' => $stock_after,
                'reference_type' => 'RFI',
                'reference_id' => $rfi_id,
                'created_by' => $inspector_id
            ));
        }

        $this->Rfi_model->update($rfi_id, array(
            'status' => 'APPROVED',
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));

        $this->Rfi_model->add_approval(array(
            'rfi_id' => $rfi_id,
            'revision' => $rfi->revision,
            'inspector_id' => $inspector_id,
            'decision' => 'APPROVED',
            'remark' => NULL
        ));

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->session->set_flashdata(
                'error',
                'Gagal melakukan approval RFI.'
            );
        } else {
            $this->db->trans_commit();
            $this->session->set_flashdata(
                'success',
                'RFI berhasil di-approve dan stock material telah diperbarui.'
            );
        }

        redirect('inspector/detail/' . $rfi_id);
    }
}