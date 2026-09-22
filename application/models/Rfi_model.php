<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rfi_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    // RFI MODEL //
    public function get_all()
    {
        $this->db->select('
            rfis.*,
            projects.project_code,
            projects.project_name,
            users.name AS requester_name,
            inspectors.name AS inspector_name
        ');

        $this->db->from('rfis');
        $this->db->join('projects', 'projects.id = rfis.project_id');
        $this->db->join('users', 'users.id = rfis.requester_id');
        $this->db->join('users AS inspectors', 'inspectors.id = rfis.inspector_id');
        $this->db->order_by('rfis.created_at', 'DESC');

        return $this->db->get()->result();
    }

    public function get_all_by_requester($requester_id)
    {
        $this->db->select('
            rfis.*,
            projects.project_code,
            projects.project_name,
            users.name AS requester_name,
            inspectors.name AS inspector_name
        ');

        $this->db->from('rfis');
        $this->db->join('projects', 'projects.id = rfis.project_id');
        $this->db->join('users', 'users.id = rfis.requester_id');
        $this->db->join('users AS inspectors', 'inspectors.id = rfis.inspector_id');
        $this->db->where('rfis.requester_id', $requester_id);
        $this->db->order_by('rfis.created_at', 'DESC');

        return $this->db->get()->result();
    }

    public function get_by_inspector($inspector_id)
    {
        $this->db->select('
            rfis.*,
            projects.project_code,
            projects.project_name,
            users.name AS requester_name,
            inspectors.name AS inspector_name
        ');

        $this->db->from('rfis');
        $this->db->join('projects', 'projects.id = rfis.project_id');
        $this->db->join('users', 'users.id = rfis.requester_id');
        $this->db->join('users AS inspectors', 'inspectors.id = rfis.inspector_id');
        $this->db->where('rfis.inspector_id', $inspector_id);
        $this->db->order_by('rfis.created_at', 'DESC');

        return $this->db->get()->result();
    }

    public function get_submitted()
    {
        $this->db->select('
            rfis.*,
            projects.project_code,
            projects.project_name,
            users.name AS requester_name,
            inspectors.name AS inspector_name
        ');

        $this->db->from('rfis');
        $this->db->join('projects', 'projects.id = rfis.project_id');
        $this->db->join('users', 'users.id = rfis.requester_id');
        $this->db->join('users AS inspectors', 'inspectors.id = rfis.inspector_id');
        $this->db->where('rfis.status', 'SUBMITTED');
        $this->db->order_by('rfis.submitted_at', 'DESC');

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('
            rfis.*,
            projects.project_code,
            projects.project_name,
            users.name AS requester_name,
            inspectors.name AS inspector_name
        ');

        $this->db->from('rfis');
        $this->db->join('projects', 'projects.id = rfis.project_id');
        $this->db->join('users', 'users.id = rfis.requester_id');
        $this->db->join('users AS inspectors', 'inspectors.id = rfis.inspector_id');
        $this->db->where('rfis.id', $id);

        return $this->db->get()->row();
    }

    public function create($data)
    {
        $this->db->insert('rfis', $data);

        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);

        return $this->db->update('rfis', $data);
    }

    public function delete($id)
    {
        return $this->db->delete('rfis', array(
            'id' => $id
        ));
    }

    public function get_next_number()
    {
        $this->db->select('COUNT(*) + 1 AS next_number');
        $this->db->where("EXTRACT(YEAR FROM created_at) = " . date('Y'));

        $result = $this->db->get('rfis')->row();

        return $result ? $result->next_number : 1;
    }

    public function get_items($rfi_id)
    {
        $this->db->select('
            rfi_items.*,
            materials.material_code,
            materials.material_name AS master_material_name,
            materials.quantity AS available_qty,
            materials.unit AS master_uom,
            users.name AS checker_name
        ');
        $this->db->from('rfi_items');
        $this->db->join('materials', 'materials.id = rfi_items.material_id');
        $this->db->join('users', 'users.id = rfi_items.checked_by', 'left');
        $this->db->where('rfi_items.rfi_id', $rfi_id);
        $this->db->order_by('rfi_items.id', 'ASC');

        return $this->db->get()->result();
    }

    public function get_item_by_id($item_id)
    {
        return $this->db->get_where('rfi_items', array(
            'id' => $item_id
        ))->row();
    }

    public function add_item($data)
    {
        return $this->db->insert('rfi_items', $data);
    }

    public function update_item($item_id, $data)
    {
        $this->db->where('id', $item_id);

        return $this->db->update('rfi_items', $data);
    }

    public function delete_item($item_id)
    {
        return $this->db->delete('rfi_items', array(
            'id' => $item_id
        ));
    }

    public function delete_items($rfi_id)
    {
        return $this->db->delete('rfi_items', array(
            'rfi_id' => $rfi_id
        ));
    }

    public function calculate_item_quantity($item_id)
    {
        $this->db->select('
            rfi_items.id,
            rfi_items.material_id,
            rfi_items.requested_qty,
            rfi_items.approved_qty,
            rfi_items.status,
            materials.material_code,
            materials.material_name,
            materials.quantity AS available_qty,
            materials.unit
        ');
        $this->db->from('rfi_items');
        $this->db->join('materials', 'materials.id = rfi_items.material_id');
        $this->db->where('rfi_items.id', $item_id);

        $item = $this->db->get()->row();

        if (!$item) {
            return NULL;
        }

        $requested_qty = (float) $item->requested_qty;
        $available_qty = (float) $item->available_qty;
        $approved_qty = min($requested_qty, $available_qty);
        $shortage_qty = max($requested_qty - $available_qty, 0);

        $item->requested_qty = $requested_qty;
        $item->available_qty = $available_qty;
        $item->approved_qty = $approved_qty;
        $item->shortage_qty = $shortage_qty;

        return $item;
    }

    public function get_project()
    {
        $this->db->where('is_active', TRUE);
        $this->db->order_by('project_code', 'ASC');

        return $this->db->get('projects')->result();
    }

    public function get_materials()
    {
        $this->db->where('is_active', TRUE);
        $this->db->order_by('material_name', 'ASC');

        return $this->db->get('materials')->result();
    }

    public function get_material_by_id($id)
    {
        return $this->db->get_where('materials', array(
            'id' => $id,
            'is_active' => TRUE
        ))->row();
    }

    public function get_inspectors()
    {
        $this->db->select('
            users.id,
            users.username,
            users.name,
            users.email
        ');

        $this->db->from('users');
        $this->db->join('roles', 'roles.id = users.role_id');
        $this->db->where('roles.role_code', 'INSPECTOR');
        $this->db->where('users.is_active', TRUE);
        $this->db->order_by('users.name', 'ASC');

        return $this->db->get()->result();
    }

    public function get_revisions($rfi_id)
    {
        $this->db->select('
            rfi_revisions.*,
            users.name AS creator_name
        ');

        $this->db->from('rfi_revisions');
        $this->db->join('users', 'users.id = rfi_revisions.created_by');
        $this->db->where('rfi_revisions.rfi_id', $rfi_id);
        $this->db->order_by('rfi_revisions.revision', 'ASC');

        return $this->db->get()->result();
    }

    public function get_revision_by_id($revision_id)
    {
        return $this->db->get_where('rfi_revisions', array(
            'id' => $revision_id
        ))->row();
    }

    public function add_revision($data)
    {
        $this->db->insert('rfi_revisions', $data);

        return $this->db->insert_id();
    }

    public function get_revision_items($rfi_revision_id)
    {
        return $this->db
            ->get_where('rfi_revision_items', array(
                'rfi_revision_id' => $rfi_revision_id
            ))
            ->result();
    }

    public function add_revision_item($data)
    {
        return $this->db->insert('rfi_revision_items', $data);
    }

    public function get_approvals($rfi_id)
    {
        $this->db->select('
            rfi_approvals.*,
            users.name AS inspector_name
        ');

        $this->db->from('rfi_approvals');
        $this->db->join('users', 'users.id = rfi_approvals.inspector_id');
        $this->db->where('rfi_approvals.rfi_id', $rfi_id);
        $this->db->order_by('rfi_approvals.revision', 'ASC');

        return $this->db->get()->result();
    }

    public function add_approval($data)
    {
        return $this->db->insert('rfi_approvals', $data);
    }


    
    // PROJECT MODEL //
    public function get_projects()
    {
        $this->db->where('is_active', TRUE);
        $this->db->order_by('project_code', 'ASC');

        return $this->db->get('projects')->result();
    }

    public function get_project_by_id($id)
    {
        return $this->db->get_where('projects', array(
            'id' => $id
        ))->row();
    }

    public function get_project_by_code($project_code)
    {
        return $this->db->get_where('projects', array(
            'project_code' => $project_code
        ))->row();
    }

    public function create_project($data)
    {
        $this->db->insert('projects', $data);

        return $this->db->insert_id();
    }

    public function update_project($id, $data)
    {
        $this->db->where('id', $id);

        return $this->db->update('projects', $data);
    }

    public function delete_project($id)
    {
        return $this->db->delete('projects', array(
            'id' => $id
        ));
    }
}