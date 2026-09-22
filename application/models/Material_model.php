<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Material_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all()
    {
        $this->db->order_by('material_code', 'ASC');

        return $this->db->get('materials')->result();
    }

    public function get_active()
    {
        $this->db->where('is_active', TRUE);
        $this->db->order_by('material_code', 'ASC');

        return $this->db->get('materials')->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('materials', array(
            'id' => $id
        ))->row();
    }

    public function get_active_by_id($id)
    {
        return $this->db->get_where('materials', array(
            'id' => $id,
            'is_active' => TRUE
        ))->row();
    }

    public function get_by_code($material_code)
    {
        return $this->db->get_where('materials', array(
            'material_code' => $material_code
        ))->row();
    }

    public function get_next_code()
    {
        $result = $this->db->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(material_code FROM 5) AS INTEGER)), 0) + 1 AS next_number
             FROM materials
             WHERE material_code ~ '^MAT-[0-9]+$'"
        )->row();

        return 'MAT-' . str_pad((int) $result->next_number, 4, '0', STR_PAD_LEFT);
    }

    public function create($data)
    {
        $this->db->insert('materials', $data);

        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);

        return $this->db->update('materials', $data);
    }

    public function delete($id)
    {
        return $this->db->delete('materials', array(
            'id' => $id
        ));
    }

    public function update_quantity($id, $quantity)
    {
        $this->db->where('id', $id);

        return $this->db->update('materials', array(
            'quantity' => $quantity
        ));
    }


    public function get_transactions($material_id)
    {
        $this->db->select('
            material_transactions.*,
            users.name AS creator_name
        ');

        $this->db->from('material_transactions');
        $this->db->join(
            'users',
            'users.id = material_transactions.created_by',
            'left'
        );
        $this->db->where('material_transactions.material_id', $material_id);
        $this->db->order_by('material_transactions.created_at', 'DESC');

        return $this->db->get()->result();
    }

    public function add_transaction($data)
    {
        $this->db->insert('material_transactions', $data);

        return $this->db->insert_id();
    }
}
