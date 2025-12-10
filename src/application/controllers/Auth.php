<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // If user is logged in, redirect to dashboard
        if ($this->is_logged_in && $this->uri->segment(2) !== 'logout') {
            redirect('dashboard');
        }
        
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('User_model');
    }
    
    public function index() {
        $this->login();
    }
    
    public function login() {
        // کاربر لاگین کرده نباشد
        $this->redirect_if_logged_in();
        
        $data = [
            'page_title' => 'ورود به سیستم',
            'error' => null,
            'success' => $this->session->flashdata('success')
        ];
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('email', 'ایمیل', 'required|valid_email');
            $this->form_validation->set_rules('password', 'رمز عبور', 'required');
            
            if ($this->form_validation->run()) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                
                $user = $this->User_model->get_by_email($email);
                
                if ($user && password_verify($password, $user->password)) {
                    // Set session
                    $this->session->set_userdata([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'logged_in' => true
                    ]);
                    
                    // Remember me
                    if ($this->input->post('remember_me')) {
                        $token = bin2hex(random_bytes(16));
                        set_cookie('remember_token', $token, 30*24*60*60); // 30 days
                        $this->User_model->update_remember_token($user->id, $token);
                    }
                    
                    redirect('dashboard');
                    return;
                } else {
                    $data['error'] = 'ایمیل یا رمز عبور اشتباه است';
                }
            }
        }
        
        $this->render('auth/login', $data);
    }
    
    public function register() {
        // کاربر لاگین کرده نباشد
        $this->redirect_if_logged_in();
        
        $data = [
            'page_title' => 'ثبت نام',
            'error' => null
        ];
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('first_name', 'نام', 'required|min_length[2]');
            $this->form_validation->set_rules('last_name', 'نام خانوادگی', 'required|min_length[2]');
            $this->form_validation->set_rules('email', 'ایمیل', 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password', 'رمز عبور', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'تکرار رمز عبور', 'required|matches[password]');
            
            if ($this->form_validation->run()) {
                $user_data = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'email' => $this->input->post('email'),
                    'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                if ($this->User_model->create($user_data)) {
                    $this->session->set_flashdata('success', 'ثبت نام موفق بود. لطفاً وارد شوید.');
                    redirect('auth/login');
                } else {
                    $data['error'] = 'خطا در ایجاد حساب کاربری';
                }
            }
        }
        
        $this->render('auth/register', $data);
    }
    
    public function logout() {
        $this->session->sess_destroy();
        delete_cookie('remember_token');
        redirect('auth/login');
    }
}