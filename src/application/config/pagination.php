<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['pagination'] = [
    'use_page_numbers'     => TRUE,
    'uri_segment'          => 3,
    'per_page'             => 10,

    // استایل‌دهی فارسی و بوت‌استرپ
    'full_tag_open'        => '<ul class="pagination justify-content-center mt-4">',
    'full_tag_close'       => '</ul>',

    'first_link'           => '« ابتدا',
    'first_tag_open'       => '<li class="page-item">',
    'first_tag_close'      => '</li>',

    'last_link'            => 'انتها »',
    'last_tag_open'        => '<li class="page-item">',
    'last_tag_close'       => '</li>',

    'next_link'            => 'بعدی ›',
    'next_tag_open'        => '<li class="page-item">',
    'next_tag_close'       => '</li>',

    'prev_link'            => '‹ قبلی',
    'prev_tag_open'        => '<li class="page-item">',
    'prev_tag_close'       => '</li>',

    'cur_tag_open'         => '<li class="page-item active"><span class="page-link">',
    'cur_tag_close'        => '</span></li>',

    'num_tag_open'         => '<li class="page-item">',
    'num_tag_close'        => '</li>',

    'attributes'           => ['class' => 'page-link'],
];