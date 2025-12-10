<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Testauth extends CI_Controller {
    
    public function index() {
        echo "<h1>Test Auth - No BaseController</h1>";
        echo "If this works, problem is with BaseController";
        echo "<br><a href='" . site_url('auth') . "'>Try Auth Controller</a>";
    }
}