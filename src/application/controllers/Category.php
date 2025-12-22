<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Category_model');
        $this->require_login();
    }

    // public function index() {
    //     $categories = $this->Category_model->get_by_user($this->user_id);
    //     echo json_encode($categories);
    // }

    // ایجاد دسته‌بندی جدید
public function create() {
    header('Content-Type: application/json');

    $name = trim($this->input->post('name'));
    $type = $this->input->post('type');

    if (empty($name) || empty($type)) {
        http_response_code(422);
        echo json_encode(['message' => 'نام و نوع دسته‌بندی الزامی است.']);
        return;
    }

    if ($this->Category_model->exists_by_user($this->user_id, $type, $name)) {
        http_response_code(422);
        echo json_encode(['message' => 'دسته‌بندی‌ای با این نام و نوع از قبل وجود دارد.']);
        return;
    }

    $data = [
        'user_id' => $this->user_id,
        'name' => $name,
        'type' => $type,
        'color' => '#6c757d'
    ];

    if ($this->Category_model->create($data)) {
        echo json_encode(['status' => 'success', 'message' => 'دسته‌بندی اضافه شد.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'خطا در ایجاد دسته‌بندی.']);
    }
}

        public function index() {
        $data = [
            'page_title' => 'مدیریت دسته‌بندی‌ها',
            'user' => $this->user,
            'content' => 'category/index'
        ];
        $this->load->view('layout', $data);
    }

    // API: دریافت لیست دسته‌بندی‌ها
    public function api_list() {
        $categories = $this->Category_model->get_all_for_user($this->user_id);
        echo json_encode($categories);
    }

    // API: به‌روزرسانی دسته‌بندی
public function api_update($id) {
    header('Content-Type: application/json');

    $name = trim($this->input->post('name'));
    $type = $this->input->post('type');

    if (empty($name) || empty($type)) {
        http_response_code(422);
        echo json_encode(['message' => 'نام و نوع دسته‌بندی الزامی است.']);
        return;
    }

    $category = $this->Category_model->get_by_id($id);
    if (!$category) {
        http_response_code(404);
        echo json_encode(['message' => 'دسته‌بندی یافت نشد.']);
        return;
    }

    if ($category->user_id !== null && $category->user_id != $this->user_id) {
        http_response_code(403);
        echo json_encode(['message' => 'دسترسی غیرمجاز.']);
        return;
    }

    $this->Category_model->update($id, ['name' => $name, 'type' => $type]);
    echo json_encode(['status' => 'success', 'message' => 'ویرایش موفقیت‌آمیز بود.']);
}
    // API: حذف دسته‌بندی
    public function api_delete($id) {
        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['message' => 'یافت نشد.']);
            return;
        }

        // بررسی وجود تراکنش
        if ($this->Category_model->has_transactions($id)) {
            http_response_code(400);
            echo json_encode(['message' => 'این دسته‌بندی دارای تراکنش است و قابل حذف نیست.']);
            return;
        }

        // فقط دسته‌بندی‌های شخصی قابل حذف هستند
        if ($category->user_id === null) {
            http_response_code(400);
            echo json_encode(['message' => 'دسته‌بندی‌های پیش‌فرض قابل حذف نیستند.']);
            return;
        }

        $this->Category_model->delete($id);
        echo json_encode(['status' => 'success']);
    }

    public function get_categories() {
    header('Content-Type: application/json');
    $type = $this->input->get('type'); 
    $categories = $this->Category_model->get_all_for_user($this->user_id, $type);
    echo json_encode($categories);
}


}