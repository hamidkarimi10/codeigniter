<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_ip extends CI_Controller {
    public function index() {
        echo "Your IP address is: " . $_SERVER['REMOTE_ADDR'];
        echo "<br>Server IP: " . $_SERVER['SERVER_ADDR'];
        echo "<br>HTTP CLIENT IP: " . $_SERVER['HTTP_CLIENT_IP'] ?? 'Not set';
        echo "<br>HTTP X FORWARDED FOR: " . $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'Not set';
    }
}