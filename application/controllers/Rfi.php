<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rfi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        require_role('DOCONT');
        $this->load->model('Rfi_model');
        $this->load->model('Material_model');
    }

    public function index()
    {
        $data['rfis'] = $this->Rfi_model->get_all_by_requester(
            $this->session->userdata('user_id')
        );

        $this->load->view('rfi/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $project_id = (int) $this->input->post('project_id');
            $inspector_id = (int) $this->input->post('inspector_id');

            if (!$project_id || !$inspector_id) {
                show_error(
                    'Project dan Inspector wajib dipilih.',
                    400,
                    'Data Tidak Lengkap'
                );
            }

            $project = $this->Rfi_model->get_project_by_id($project_id);
            $inspectors = $this->Rfi_model->get_inspectors();
            $valid_inspector = FALSE;

            foreach ($inspectors as $inspector) {
                if ($inspector->id == $inspector_id) {
                    $valid_inspector = TRUE;
                    break;
                }
            }

            if (!$project || !$valid_inspector) {
                show_error(
                    'Project atau Inspector tidak valid.',
                    400,
                    'Data Tidak Valid'
                );
            }

            $year = date('Y');
            $number = $this->Rfi_model->get_next_number();
            $rfi_number = 'RFI-' . $year . '-' .
                str_pad($number, 4, '0', STR_PAD_LEFT);
            $now = date('Y-m-d H:i:s');

            $data = array(
                'rfi_number'   => $rfi_number,
                'revision'     => 0,
                'project_id'   => $project_id,
                'requester_id' => $this->session->userdata('user_id'),
                'inspector_id' => $inspector_id,
                'status'       => 'DRAFT',
                'created_at'   => $now,
                'updated_at'   => $now
            );

            $id = $this->Rfi_model->create($data);

            if (!$id) {
                show_error(
                    'Gagal membuat RFI.',
                    500,
                    'RFI Gagal Dibuat'
                );
            }

            redirect('rfi/edit/' . $id);
            return;
        }

        $data['rfi_number'] = 'RFI-' . date('Y') . '-' .
            str_pad(
                $this->Rfi_model->get_next_number(),
                4,
                '0',
                STR_PAD_LEFT
            );
        $data['projects'] = $this->Rfi_model->get_projects();
        $data['inspectors'] = $this->Rfi_model->get_inspectors();

        $this->load->view('rfi/form', $data);
    }

    public function edit($id)
    {
        $rfi = $this->Rfi_model->get_by_id($id);

        if (
            !$rfi ||
            $rfi->requester_id != $this->session->userdata('user_id')
        ) {
            show_404();
        }

        if (!in_array($rfi->status, array(
            'DRAFT',
            'REVISION_REQUIRED'
        ))) {
            show_error(
                'RFI tidak dapat diedit pada status saat ini.',
                400,
                'RFI Tidak Dapat Diedit'
            );
        }

        $data['rfi'] = $rfi;
        $data['items'] = $this->Rfi_model->get_items($id);
        $data['materials'] = $this->Rfi_model->get_materials();

        $this->load->view('rfi/form', $data);
    }

    public function add_item($rfi_id)
    {
        if ($this->input->method() !== 'post') {
            show_error(
                'Method tidak diizinkan.',
                405,
                'Method Tidak Diizinkan'
            );
        }

        $rfi = $this->Rfi_model->get_by_id($rfi_id);

        if (
            !$rfi ||
            $rfi->requester_id != $this->session->userdata('user_id')
        ) {
            show_404();
        }

        if (!in_array($rfi->status, array(
            'DRAFT',
            'REVISION_REQUIRED'
        ))) {
            show_error(
                'Item RFI tidak dapat ditambahkan pada status saat ini.',
                400,
                'RFI Tidak Dapat Diedit'
            );
        }

        $material_id = (int) $this->input->post('material_id');
        $requested_qty = (float) $this->input->post('requested_qty');

        if (!$material_id) {
            show_error(
                'Material wajib dipilih.',
                400,
                'Material Tidak Valid'
            );
        }

        if ($requested_qty <= 0) {
            show_error(
                'Quantity harus lebih dari 0.',
                400,
                'Quantity Tidak Valid'
            );
        }

        $material = $this->Material_model->get_active_by_id($material_id);

        if (!$material) {
            show_error(
                'Material tidak ditemukan.',
                400,
                'Material Tidak Valid'
            );
        }

        $data = array(
            'rfi_id'        => $rfi_id,
            'revision'      => $rfi->revision,
            'material_id'   => $material->id,
            'material_name' => $material->material_name,
            'requested_qty' => $requested_qty,
            'approved_qty'  => 0,
            'uom'           => $material->unit,
            'status'        => 'PENDING'
        );

        if (!$this->Rfi_model->add_item($data)) {
            show_error(
                'Gagal menambahkan material ke RFI.',
                500,
                'Item Gagal Ditambahkan'
            );
        }

        redirect('rfi/edit/' . $rfi_id);
    }

    public function update_item($rfi_id, $item_id)
    {
        if ($this->input->method() !== 'post') {
            show_error(
                'Method tidak diizinkan.',
                405,
                'Method Tidak Diizinkan'
            );
        }

        $rfi = $this->Rfi_model->get_by_id($rfi_id);
        $item = $this->Rfi_model->get_item_by_id($item_id);

        if (
            !$rfi ||
            !$item ||
            $rfi->requester_id != $this->session->userdata('user_id') ||
            $item->rfi_id != $rfi_id
        ) {
            show_404();
        }

        if (!in_array($rfi->status, array(
            'DRAFT',
            'REVISION_REQUIRED'
        ))) {
            show_error(
                'Item RFI tidak dapat diubah pada status saat ini.',
                400,
                'RFI Tidak Dapat Diedit'
            );
        }

        $requested_qty = (float) $this->input->post('requested_qty');

        if ($requested_qty <= 0) {
            show_error(
                'Quantity harus lebih dari 0.',
                400,
                'Quantity Tidak Valid'
            );
        }

        if (!$this->Rfi_model->update_item($item_id, array(
            'requested_qty' => $requested_qty,
            'status'        => 'PENDING',
            'approved_qty'  => 0,
            'remark'        => NULL,
            'checked_by'    => NULL,
            'checked_at'    => NULL,
            'updated_at'    => date('Y-m-d H:i:s')
        ))) {
            show_error(
                'Gagal memperbarui item RFI.',
                500,
                'Item Gagal Diperbarui'
            );
        }

        redirect('rfi/edit/' . $rfi_id);
    }

    public function delete_item($rfi_id, $item_id)
    {
        if ($this->input->method() !== 'post') {
            show_error(
                'Method tidak diizinkan.',
                405,
                'Method Tidak Diizinkan'
            );
        }

        $rfi = $this->Rfi_model->get_by_id($rfi_id);
        $item = $this->Rfi_model->get_item_by_id($item_id);

        if (
            !$rfi ||
            !$item ||
            $rfi->requester_id != $this->session->userdata('user_id') ||
            $item->rfi_id != $rfi_id
        ) {
            show_404();
        }

        if (!in_array($rfi->status, array(
            'DRAFT',
            'REVISION_REQUIRED'
        ))) {
            show_error(
                'Item RFI tidak dapat dihapus pada status saat ini.',
                400,
                'RFI Tidak Dapat Diedit'
            );
        }

        if (!$this->Rfi_model->delete_item($item_id)) {
            show_error(
                'Gagal menghapus item RFI.',
                500,
                'Item Gagal Dihapus'
            );
        }

        redirect('rfi/edit/' . $rfi_id);
    }

    public function submit($id)
    {
        if ($this->input->method() !== 'post') {
            show_error(
                'Method tidak diizinkan.',
                405,
                'Method Tidak Diizinkan'
            );
        }

        $rfi = $this->Rfi_model->get_by_id($id);

        if (
            !$rfi ||
            $rfi->requester_id != $this->session->userdata('user_id')
        ) {
            show_404();
        }

        if (!in_array($rfi->status, array(
            'DRAFT',
            'REVISION_REQUIRED'
        ))) {
            show_error(
                'RFI tidak dapat disubmit pada status saat ini.',
                400,
                'RFI Tidak Dapat Disubmit'
            );
        }

        $items = $this->Rfi_model->get_items($id);

        if (!$items) {
            show_error(
                'RFI harus memiliki minimal satu material.',
                400,
                'RFI Belum Lengkap'
            );
        }

        $now = date('Y-m-d H:i:s');

        if (!$this->Rfi_model->update($id, array(
            'status'       => 'SUBMITTED',
            'submitted_at' => $now,
            'updated_at'   => $now
        ))) {
            show_error(
                'Gagal melakukan submit RFI.',
                500,
                'Submit RFI Gagal'
            );
        }

        $this->session->set_flashdata(
            'success',
            'RFI berhasil disubmit kepada Inspector.'
        );

        redirect('rfi');
    }

    public function detail($id)
    {
        $rfi = $this->Rfi_model->get_by_id($id);

        if (
            !$rfi ||
            $rfi->requester_id != $this->session->userdata('user_id')
        ) {
            show_404();
        }

        $data['rfi'] = $rfi;
        $data['items'] = $this->Rfi_model->get_items($id);
        $data['revisions'] = $this->Rfi_model->get_revisions($id);
        $data['approvals'] = $this->Rfi_model->get_approvals($id);

        $this->load->view('rfi/detail', $data);
    }

    public function revise($id)
    {
        if ($this->input->method() !== 'post') {
            show_error(
                'Method tidak diizinkan.',
                405,
                'Method Tidak Diizinkan'
            );
        }

        $rfi = $this->Rfi_model->get_by_id($id);

        if (
            !$rfi ||
            $rfi->requester_id != $this->session->userdata('user_id')
        ) {
            show_404();
        }

        if ($rfi->status !== 'REVISION_REQUIRED') {
            show_error(
                'RFI tidak dapat direvisi pada status saat ini.',
                400,
                'RFI Tidak Dapat Direvisi'
            );
        }

        $items = $this->Rfi_model->get_items($id);

        if (!$items) {
            show_error(
                'RFI tidak memiliki item material.',
                400,
                'RFI Tidak Valid'
            );
        }

        $new_revision = $rfi->revision + 1;
        $now = date('Y-m-d H:i:s');
        $requester_id = $this->session->userdata('user_id');

        $this->db->trans_begin();

        $revision_id = $this->Rfi_model->add_revision(array(
            'rfi_id'     => $id,
            'revision'   => $new_revision,
            'reason'     => NULL,
            'created_by' => $requester_id
        ));

        if (!$revision_id) {
            $this->db->trans_rollback();

            show_error(
                'Gagal membuat revision RFI.',
                500,
                'Revision Gagal'
            );
        }

        foreach ($items as $item) {
            if (!$this->Rfi_model->add_revision_item(array(
                'rfi_revision_id' => $revision_id,
                'material_id'     => $item->material_id,
                'material_name'   => $item->material_name,
                'requested_qty'   => $item->requested_qty,
                'uom'             => $item->uom
            ))) {
                $this->db->trans_rollback();

                show_error(
                    'Gagal menyimpan item revision RFI.',
                    500,
                    'Revision Gagal'
                );
            }
        }

        if (!$this->Rfi_model->update($id, array(
            'revision'     => $new_revision,
            'status'       => 'DRAFT',
            'submitted_at' => NULL,
            'approved_at'  => NULL,
            'rejected_at'  => NULL,
            'updated_at'   => $now
        ))) {
            $this->db->trans_rollback();

            show_error(
                'Gagal memperbarui revision RFI.',
                500,
                'Revision Gagal'
            );
        }

        foreach ($items as $item) {
            if (!$this->Rfi_model->update_item($item->id, array(
                'revision'     => $new_revision,
                'approved_qty' => 0,
                'status'       => 'PENDING',
                'remark'       => NULL,
                'checked_by'   => NULL,
                'checked_at'   => NULL,
                'updated_at'   => $now
            ))) {
                $this->db->trans_rollback();

                show_error(
                    'Gagal memperbarui item revision RFI.',
                    500,
                    'Revision Gagal'
                );
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            show_error(
                'Gagal melakukan revision RFI.',
                500,
                'Revision Gagal'
            );
        }

        $this->db->trans_commit();

        $this->session->set_flashdata(
            'success',
            'RFI berhasil dibuat menjadi Revision ' . $new_revision . '.'
        );

        redirect('rfi/edit/' . $id);
    }
}