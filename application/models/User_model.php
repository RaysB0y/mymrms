<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_username($username)
    {
        return $this->db->get_where('users', array('username' => $username))->row();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('users', array('id' => $id))->row();
    }

    public function get_by_role($role_id)
    {
        return $this->db->get_where('users', array(
            'role_id' => $role_id,
            'is_active' => TRUE
        ))->result();
    }

    public function get_role_by_id($role_id)
    {
        return $this->db->get_where('roles', array(
            'id' => $role_id
        ))->row();
    }

    public function get_role_by_code($role_code)
    {
        return $this->db->get_where('roles', array(
            'role_code' => $role_code
        ))->row();
    }

    public function get_roles()
    {
        return $this->db->get('roles')->result();
    }

    public function get_roles_by_department($department_id)
    {
        return $this->db->get_where('roles', array(
            'department_id' => $department_id,
            'is_active' => TRUE
        ))->result();
    }

    public function get_all()
    {
        return $this->db->get('users')->result();
    }

    public function create($data)
    {
        return $this->db->insert('users', $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    public function delete($id)
    {
        return $this->db->delete('users', array('id' => $id));
    }
}