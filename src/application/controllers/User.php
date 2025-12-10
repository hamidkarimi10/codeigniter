<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
    }
    
    public function profile() {
        $this->require_login();
        $data = [
            'page_title' => 'پروفایل کاربری',
            'user' => $this->user,
            'content' => 'user/profile'
        ];
        
        $this->load->view('layout', $data);
    }
    
    public function update() {
        $this->require_login();
        
        $this->form_validation->set_rules('first_name', 'نام', 'required|min_length[2]');
        $this->form_validation->set_rules('last_name', 'نام خانوادگی', 'required|min_length[2]');
        $this->form_validation->set_rules('email', 'ایمیل', 'required|valid_email');
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('user/profile');
            return;
        }
        
        $update_data = [
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email' => $this->input->post('email'),
        ];
        
        $password = $this->input->post('password');
        $password_confirm = $this->input->post('password_confirm');
        
        if (!empty($password)) {
            if ($password === $password_confirm) {
                $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $this->session->set_flashdata('error', 'تایید رمز عبور مطابقت ندارد.');
                redirect('user/profile');
                return;
            }
        }
        
        if (!empty($_FILES['avatar']['name'])) {
            $config['upload_path'] = './uploads/avatars/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['file_name'] = 'avatar_'.$this->user_id.'_'.time();
            $config['max_size'] = 2048;
            
            $this->load->library('upload', $config);
            
            if (!$this->upload->do_upload('avatar')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('user/profile');
                return;
            } else {
                $file_data = $this->upload->data();
                $update_data['avatar'] = 'uploads/avatars/' . $file_data['file_name'];
            }
        }
        
        if ($this->User_model->update($this->user_id, $update_data)) {
            $this->session->set_flashdata('success', 'اطلاعات با موفقیت بروزرسانی شد.');
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'خطا در بروزرسانی اطلاعات.');
            redirect('user/profile');
        }
    }
}