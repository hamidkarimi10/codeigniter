<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Category_model');
    }
    
    public function index() {
        $categories = $this->Category_model->get_by_user($this->user_id);
        
        $data = [
            'page_title' => 'مدیریت دسته‌بندی‌ها',
             'user' => $this->user, 
            'categories' => $categories,
            'content' => 'categories/index'
        ];
        
        $this->load->view('layout', $data);
    }
    
    public function create() {
        $data = [
            'page_title' => 'ایجاد دسته‌بندی جدید',
            'user' => $this->user,
            'content' => 'categories/create'
        ];
        
        $this->load->view('layout', $data);
    }
    
    public function edit($category_id) {
        $data = [
            'page_title' => 'ویرایش دسته‌بندی',
            'user' => $this->user,
            'content' => 'categories/edit'
        ];
        
        $this->load->view('layout', $data);
    }
    
    public function get_by_type() {
        $type = $this->input->get('type');
        $user_id = $this->input->get('user_id');
        
        if (!$type || !in_array($type, ['income','expense'])) {
            http_response_code(400);
            echo json_encode(['error' => 'نوع تراکنش نامعتبر است']);
            return;
        }
        
        if (!$user_id || !is_numeric($user_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'شناسه کاربر نامعتبر است']);
            return;
        }
        
        $categories = $type == 'income' 
            ? $this->Category_model->get_income_categories($user_id)
            : $this->Category_model->get_expense_categories($user_id);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($categories));
    }
    public function get_categories() {
        $type = $this->input->get('type'); 
        $categories = $type ? $this->Category_model->get_by_type($type) : $this->Category_model->get_all();
        echo json_encode($categories);
    }
}