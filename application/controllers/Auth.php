<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('session');
    }

    public function index()
    {
        if ($this->session->userdata('user_id')) {
            $this->_redirect_by_role();
            return;
        }

        $this->login();
    }

    public function login()
    {
        if ($this->session->userdata('user_id')) {
            $this->_redirect_by_role();
            return;
        }

        if ($this->input->method() === 'post') {
            $username = trim($this->input->post('username', TRUE));
            $password = $this->input->post('password');

            $user = $this->User_model->get_by_username($username);
print_r($user); // Debugging line to check the user object
            if ($user && password_verify($password, $user->password)) {
                if (!$this->_is_active($user->is_active)) {
                    $data = array(
                        'error' => 'Akun Anda sudah dinonaktifkan.'
                    );

                    $this->load->view('auth/login', $data);
                    return;
                }

                $role = $this->User_model->get_role_by_id($user->role_id);

                if (!$role || !$this->_is_active($role->is_active)) {
                    $data = array(
                        'error' => 'Role akun tidak valid atau sudah dinonaktifkan.'
                    );

                    $this->load->view('auth/login', $data);
                    return;
                }

                $this->session->set_userdata(array(
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'role_id' => $user->role_id,
                    'role' => $role->role_code,
                    'department_id' => $user->department_id,
                    'logged_in' => TRUE
                ));
                $this->session->sess_regenerate(TRUE);

                $this->_redirect_by_role();
                return;
            }

            $data = array(
                'error' => 'Username atau password salah.'
            );

            $this->load->view('auth/login', $data);
            return;
        }

        $this->load->view('auth/login');
    }


    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    private function _redirect_by_role()
    {
        $role = $this->session->userdata('role');

        if ($role === 'DOCONT') {
            redirect('rfi');
            return;
        }

        if ($role === 'INSPECTOR') {
            redirect('inspector');
            return;
        }

        if ($role === 'MATERIAL_MAN') {
            redirect('warehouse');
            return;
        }

        $this->session->sess_destroy();
        redirect('auth/login');
    }

    private function _is_active($value)
    {
        return in_array($value, array(TRUE, 1, '1', 't', 'true'), TRUE);
    }
}
