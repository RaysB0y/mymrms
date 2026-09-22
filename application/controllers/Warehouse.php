<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse extends CI_Controller
{
    private $standard_uoms = array(
        'PCS', 'UNIT', 'SET', 'PAIR', 'BOX', 'BAG', 'ROLL', 'SHEET',
        'KG', 'G', 'TON', 'L', 'ML', 'M', 'CM', 'MM', 'M2', 'M3', 'PAIL'
    );

    public function __construct()
    {
        parent::__construct();
        require_role('MATERIAL_MAN');
        $this->load->model('Material_model');
    }

    public function index()
    {
        $data['materials'] = $this->Material_model->get_all();
        $data['next_material_code'] = $this->Material_model->get_next_code();
        $data['standard_uoms'] = $this->standard_uoms;

        $this->load->view('warehouse/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $material_name = trim($this->input->post('material_name', TRUE));
            $unit = strtoupper(trim($this->input->post('unit', TRUE)));
            $quantity = (float) $this->input->post('quantity');

            if ($material_name === '' || !$unit) {
                show_error('Nama material dan UoM wajib diisi.', 400, 'Data Tidak Lengkap');
            }

            if (!in_array($unit, $this->standard_uoms, TRUE)) {
                show_error('UoM yang dipilih tidak valid.', 400, 'UoM Tidak Valid');
            }

            if ($quantity < 0) {
                show_error('Quantity material tidak boleh kurang dari 0.', 400, 'Quantity Tidak Valid');
            }

            // Lock membuat nomor MAT-xxxx tetap unik saat dua user menyimpan bersamaan.
            $this->db->trans_begin();
            $this->db->query('LOCK TABLE materials IN EXCLUSIVE MODE');

            $data = array(
                'material_code' => $this->Material_model->get_next_code(),
                'material_name' => $material_name,
                'unit' => $unit,
                'quantity' => $quantity,
                'is_active' => TRUE
            );

            if (!$this->Material_model->create($data)) {
                $this->db->trans_rollback();
                show_error(
                    'Gagal menambahkan material.',
                    500,
                    'Material Gagal Ditambahkan'
                );
            }

            $this->db->trans_commit();
            $this->session->set_flashdata('success', 'Material ' . $data['material_code'] . ' berhasil ditambahkan.');

            redirect('warehouse');
            return;
        }

        redirect('warehouse');
    }

    public function edit($id)
    {
        $material = $this->Material_model->get_by_id($id);

        if (!$material) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $material_name = trim(
                $this->input->post('material_name', TRUE)
            );

            $unit = trim(
                $this->input->post('unit', TRUE)
            );

            $quantity = (float) $this->input->post('quantity');

            if (
                $material_name === '' ||
                $unit === ''
            ) {
                show_error(
                    'Data material wajib diisi.',
                    400,
                    'Data Tidak Lengkap'
                );
            }

            if ($quantity < 0) {
                show_error(
                    'Quantity material tidak boleh kurang dari 0.',
                    400,
                    'Quantity Tidak Valid'
                );
            }

            $unit = strtoupper($unit);

            if (!in_array($unit, $this->standard_uoms, TRUE)) {
                show_error('UoM yang dipilih tidak valid.', 400, 'UoM Tidak Valid');
            }

            $data = array(
                'material_name' => $material_name,
                'unit' => $unit,
                'quantity' => $quantity,
                'updated_at' => date('Y-m-d H:i:s')
            );

            if (!$this->Material_model->update($id, $data)) {
                show_error(
                    'Gagal memperbarui material.',
                    500,
                    'Material Gagal Diperbarui'
                );
            }

            redirect('warehouse');
            return;
        }

        redirect('warehouse');
    }

    public function delete($id)
    {
        $material = $this->Material_model->get_by_id($id);

        if (!$material) {
            show_404();
        }

        if ($this->input->method() !== 'post') {
            show_error(
                'Method tidak diizinkan.',
                405,
                'Method Tidak Diizinkan'
            );
        }

        if (!$this->Material_model->update($id, array(
            'is_active' => FALSE,
            'updated_at' => date('Y-m-d H:i:s')
        ))) {
            show_error(
                'Gagal menonaktifkan material.',
                500,
                'Material Gagal Dinonaktifkan'
            );
        }

        redirect('warehouse');
    }

    public function transactions($id)
    {
        $material = $this->Material_model->get_by_id($id);

        if (!$material) {
            show_404();
        }

        $data['material'] = $material;
        $data['transactions'] = $this->Material_model->get_transactions($id);

        $this->load->view('warehouse/transactions', $data);
    }
}
