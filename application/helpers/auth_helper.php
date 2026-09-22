<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('require_login')) {
    function require_login()
    {
        $CI =& get_instance();

        if (!$CI->session->userdata('logged_in')) {
            redirect('auth/login');
            exit;
        }
    }
}

if (!function_exists('require_role')) {
    function require_role($role)
    {
        $CI =& get_instance();

        require_login();

        if ($CI->session->userdata('role') !== $role) {
            show_error(
                'Anda tidak memiliki akses ke halaman ini.',
                403,
                'Access Denied'
            );
        }
    }
}