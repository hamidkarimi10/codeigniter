<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * تبدیل اعداد فارسی و عربی به اعداد انگلیسی (لاتین)
 *
 * مثال:
 * en_digits('۱۴۰۴/۰۹/۱۳') → '1404/09/13'
 *
 * @param string $str
 * @return string
 */
if (!function_exists('en_digits')) {
    function en_digits($str) {
        $from = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $to   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($from, $to, $str);
    }
}