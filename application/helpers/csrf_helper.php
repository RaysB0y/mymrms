<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        $CI =& get_instance();

        if (!$CI->config->item('csrf_protection')) {
            return '';
        }

        return '<input type="hidden" name="' .
            html_escape($CI->security->get_csrf_token_name()) .
            '" value="' .
            html_escape($CI->security->get_csrf_hash()) .
            '">';
    }
}