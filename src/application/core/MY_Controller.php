<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
    
    protected $user = null;
    protected $user_id = null;
    protected $is_logged_in = false;
    
    public function __construct() {
        parent::__construct();
        
        $this->load->helper(['url', 'cookie']);
        
        // Initialize user data
        $this->initialize_user();
    }
    
    protected function initialize_user() {
        // Check if session exists
        if (isset($this->session)) {
            $this->user_id = $this->session->userdata('user_id');
        } else {
            // Fallback to native PHP session
            $this->user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        }
        
        if ($this->user_id) {
            $this->is_logged_in = true;
            $this->load->model('User_model');
            $this->user = $this->User_model->get_by_id($this->user_id);
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function is_logged_in() {
        return $this->is_logged_in;
    }
    
    /**
     * Redirect to login if not authenticated
     */
    protected function require_login() {
        if (!$this->is_logged_in) {
            redirect('auth/login');
            exit;
        }
    }
    
    /**
     * Redirect to dashboard if already logged in
     * این متد گم شده بود!
     */
    protected function redirect_if_logged_in() {
        if ($this->is_logged_in) {
            redirect('dashboard');
            exit;
        }
    }
    
    /**
     * Simple logout
     */
    protected function logout() {
        if (isset($this->session)) {
            $this->session->sess_destroy();
        } else {
            session_destroy();
        }
        delete_cookie('remember_token');
        redirect('auth/login');
    }
    protected function render($view, $data = []) {
    // اضافه کردن user به همه viewها
    $data['user'] = $this->user;
    $data['user_id'] = $this->user_id;
    $data['is_logged_in'] = $this->is_logged_in;
    
    // اگر layout دارید
    if (file_exists(APPPATH . 'views/layout.php')) {
        $data['content'] = $view;
        $this->load->view('layout', $data);
    } else {
        // بدون layout
        $this->load->view($view, $data);
    }
}
}