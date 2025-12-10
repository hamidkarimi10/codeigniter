<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['Transaction_model', 'User_model', 'Category_model']);
         $this->load->helper('jdf');
         $this->load->helper('number');
    }
    
public function index() {
    $this->require_login();
    $this->load->library('pagination');


    // دریافت فیلترها از URL (GET parameters)
    $filters = [
        'search' => $this->input->get('search'),
        'type'   => $this->input->get('type'),
        'from'   => $this->input->get('from'),
        'to'     => $this->input->get('to')
    ];

    // تبدیل تاریخ‌های شمسی به میلادی (اگر وارد شده باشند)
    $from_gregorian = !empty($filters['from']) 
        ? jalali_to_gregorian_input(en_digits($filters['from'])) 
        : '';
        
    $to_gregorian = !empty($filters['to']) 
        ? jalali_to_gregorian_input(en_digits($filters['to'])) 
        : '';

    $model_filters = [
        'search' => $filters['search'],
        'type'   => $filters['type'],
        'from'   => $from_gregorian,
        'to'     => $to_gregorian
    ];

    // تنظیمات صفحه‌بندی — همه چیز به عدد تبدیل شود
    $per_page = 3;
    $page = (int) ($this->uri->segment(3) ?: 1); // پیش‌فرض صفحه ۱
    
    $offset = ($page > 1) ? ($page - 1) * $per_page : 0;

    // دریافت داده‌ها (مطمئن شویم خروجی عدد است)
    $total_rows = (int) $this->Transaction_model->count_by_filters($this->user_id, $model_filters);
    $transactions = $this->Transaction_model->get_by_filters_paginated($this->user_id, $model_filters, $per_page, $offset);

    // بارگذاری تنظیمات pagination
    $this->load->config('pagination');
    $config = $this->config->item('pagination');
    // ✅ اطمینان از عدد بودن تمام پارامترهای حساس
    $config['base_url'] = base_url('transactions/index');
    $config['total_rows'] = $total_rows;
    $config['per_page'] = (int) $per_page;
    $config['uri_segment'] = 3;
    $config['use_page_numbers'] = TRUE;

    // حفظ فیلترهای GET در لینک‌های صفحه‌بندی
    $query_string = trim($_SERVER['QUERY_STRING'] ?? '');
    if ($query_string !== '') {
        $config['suffix'] = '?' . $query_string;
        $config['first_url'] = $config['base_url'] . '/1?' . $query_string;
    }

    // راه‌اندازی pagination
    $this->pagination->initialize($config);
    $pagination_links = $this->pagination->create_links();

    // ارسال به ویو
    $data = [
        'page_title'   => 'مدیریت تراکنش‌ها',
        'user'         => $this->user,
        'transactions' => $transactions,
        'filters'      => $filters,
        'pagination'   => $pagination_links,
        'content'      => 'transactions/index'
    ];

    $this->load->view('layout', $data);
}
    public function create() {
        $this->require_login();

        $data = [
            'page_title' => 'ایجاد تراکنش جدید',
            'user' => $this->user,
            'income_categories' => $this->Category_model->get_income_categories($this->user_id),
            'expense_categories' => $this->Category_model->get_expense_categories($this->user_id),
            'content' => 'transactions/create'
        ];
        
        if ($this->input->post()) {

            $this->form_validation->set_rules('amount', 'مبلغ', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('type', 'نوع تراکنش', 'required|in_list[income,expense]');
            $this->form_validation->set_rules('category_id', 'دسته‌بندی', 'required|numeric');
            $this->form_validation->set_rules('transaction_date', 'تاریخ تراکنش', 'required');

            if ($this->form_validation->run()) {

                $transaction_data = $this->prepare_transaction_data();
                log_message('debug', 'Raw transaction date from form: ' . $transaction_data['transaction_date']);

                  // تبدیل تاریخ شمسی به میلادی
            if (!empty($transaction_data['transaction_date'])) {
                // ✅ تبدیل اعداد فارسی/عربی به انگلیسی
                $transaction_data['transaction_date'] = en_digits($transaction_data['transaction_date']);
                // ✅ تبدیل شمسی به میلادی
                // $transaction_data['transaction_date'] = jalali_to_gregorian_input($transaction_data['transaction_date']);
                    $t=explode("/" , $transaction_data['transaction_date']);
                    $r=jalali_to_gregorian ($t[0] , $t[1] , $t[2] , "-");
                    $transaction_data['transaction_date'] =$r;

            }

                if ($this->Transaction_model->create_transaction($transaction_data)) {

                    $this->recalc_user_balance();
                    $this->session->set_flashdata('success', 'تراکنش با موفقیت ایجاد شد.');

                    redirect('transactions');
                }

                $data['error'] = 'خطا در ایجاد تراکنش';
            } 
            else {
                $data['error'] = validation_errors('<div class="alert alert-danger">', '</div>');
            }
        }

        $this->load->view('layout', $data);
    }
    
    public function edit($transaction_id) {
        $this->require_login();

        $transaction = $this->Transaction_model->get_by_id($transaction_id);

        if (!$transaction || $transaction->user_id != $this->user_id) {
            show_error('تراکنش یافت نشد یا دسترسی ندارید', 404);
        }

        // تبدیل تاریخ میلادی به شمسی برای نمایش
        if (!empty($transaction->transaction_date)) {
            $transaction->jalali_date = jdate("Y/m/d",(new Datetime($transaction->transaction_date))->getTimestamp());
        } else {
            $transaction->jalali_date = '';
        }

        $data = [
            'page_title' => 'ویرایش تراکنش',
            'user' => $this->user,
            'transaction' => $transaction,
            'income_categories' => $this->Category_model->get_income_categories($this->user_id),
            'expense_categories' => $this->Category_model->get_expense_categories($this->user_id),
            'content' => 'transactions/edit'
        ];
        
        if ($this->input->post()) {

            $this->form_validation->set_rules('amount', 'مبلغ', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('type', 'نوع تراکنش', 'required|in_list[income,expense]');
            $this->form_validation->set_rules('category_id', 'دسته‌بندی', 'required|numeric');
            $this->form_validation->set_rules('transaction_date', 'تاریخ تراکنش', 'required');

            if ($this->form_validation->run()) {

                $updated_data = $this->prepare_transaction_data();

                log_message("debug", "updatet data: " . print_r($updated_data, true));


// تبدیل تاریخ شمسی به میلادی
                if (!empty($updated_data['transaction_date'])) {
                    $updated_data['transaction_date'] = en_digits($updated_data['transaction_date']);
                    // $updated_data['transaction_date'] = jalali_to_gregorian_input($updated_data['transaction_date']);
                    $t=explode("/" , $updated_data['transaction_date']);
                    $r=jalali_to_gregorian ($t[0] , $t[1] , $t[2] , "-");
                    $updated_data['transaction_date'] =$r;
                }
                log_message("debug", "updatet data: " . print_r($updated_data, true));

                if ($this->Transaction_model->update($transaction_id, $updated_data)) {

                    $this->recalc_user_balance();
                    $this->session->set_flashdata('success', 'تراکنش با موفقیت ویرایش شد.');

                    redirect('transactions');
                }

                $data['error'] = 'خطا در بروزرسانی تراکنش';
            } 
            else {
                $data['error'] = validation_errors('<div class="alert alert-danger">', '</div>');
            }
        }

        $this->load->view('layout', $data);
    }
    
    public function delete($transaction_id) {
        $this->require_login();

        $transaction = $this->Transaction_model->get_by_id($transaction_id);

        if (!$transaction || $transaction->user_id != $this->user_id) {
            show_error('تراکنش یافت نشد یا دسترسی ندارید', 404);
        }
        
        if ($this->Transaction_model->delete($transaction_id)) {

            $this->recalc_user_balance();
            $this->session->set_flashdata('success', 'تراکنش با موفقیت حذف شد.');

        } else {

            $this->session->set_flashdata('error', 'خطا در حذف تراکنش.');
        }
        
        redirect('transactions');
    }
    
    public function get_categories() {
        $this->require_login();

        $type = $this->input->get('type');

        $categories = $type === 'income'
            ? $this->Category_model->get_income_categories($this->user_id)
            : ($type === 'expense'
                ? $this->Category_model->get_expense_categories($this->user_id)
                : []);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($categories));
    }
    
    private function prepare_transaction_data() {
        return [
            'user_id'          => $this->user_id,
            'category_id'      => $this->input->post('category_id'),
            'amount'           => $this->input->post('amount'),
            'type'             => $this->input->post('type'),
            'description'      => $this->input->post('description'),
            'transaction_date' => $this->input->post('transaction_date'),
            'payment_method'   => $this->input->post('payment_method') ?: 'cash',
            'reference'        => $this->input->post('reference'),
            'status'           => 'completed'
        ];
    }
    
    private function recalc_user_balance() {
        $income  = $this->Transaction_model->get_sum_by_type($this->user_id, 'income');
        $expense = $this->Transaction_model->get_sum_by_type($this->user_id, 'expense');

        $balance = $income - $expense;

        $this->User_model->update($this->user_id, ['balance' => $balance]);

        $this->user->balance = $balance;
    }
} 

