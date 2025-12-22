<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['Transaction_model', 'User_model', 'Category_model']);
         $this->load->helper('jdf');
         $this->load->helper('number');
    }
    
public function index()
{
    $this->require_login();

    //  تشخیص درخواست AJAX (از Vue)
    if ($this->input->get('ajax') == '1') {
        // دریافت فیلترها
        $filters = [
            'search' => $this->input->get('search'),
            'type'   => $this->input->get('type'),
            'from'   => $this->input->get('from'),
            'to'     => $this->input->get('to'),
            'page'   => (int) ($this->input->get('page') ?: 1)
        ];

        // تبدیل تاریخ‌های شمسی به میلادی (همان کد قبلی)
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

        $per_page = 10;
        $offset = ($filters['page'] > 1) ? ($filters['page'] - 1) * $per_page : 0;

        $total = (int) $this->Transaction_model->count_by_filters($this->user_id, $model_filters);
        $transactions = $this->Transaction_model->get_by_filters_paginated($this->user_id, $model_filters, $per_page, $offset);

$formatted = [];
foreach ($transactions as $tx) {
    $jalali_date = '';
    if (!empty($tx->transaction_date) && $tx->transaction_date !== '0000-00-00') {
        $timestamp = strtotime($tx->transaction_date);
        if ($timestamp !== false) {
            $jalali_date = jdate('Y/m/d', $timestamp);
        }
    }

    $formatted[] = [
        'id' => $tx->id,
        'transaction_date' => $jalali_date,
        'category_name' => $tx->category_name ?? '---',
        'description' => $tx->description,
        'amount' => (int)$tx->amount,
        'type' => $tx->type
    ];
}
        // خروجی JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'transactions' => $formatted,
                'total' => $total,
                'totalPages' => ceil($total / $per_page),
                'currentPage' => $filters['page']
            ]));
        
        return;
    }

    // 🔹 اگر AJAX نبود → رفتار معمولی (صفحه HTML)
    $this->load->library('pagination');

    $filters = [
        'search' => $this->input->get('search'),
        'type'   => $this->input->get('type'),
        'from'   => $this->input->get('from'),
        'to'     => $this->input->get('to')
    ];

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

    $per_page = 3;
    $page = (int) ($this->uri->segment(3) ?: 1);
    $offset = ($page > 1) ? ($page - 1) * $per_page : 0;

    $total_rows = (int) $this->Transaction_model->count_by_filters($this->user_id, $model_filters);
    $transactions = $this->Transaction_model->get_by_filters_paginated($this->user_id, $model_filters, $per_page, $offset);

    $this->load->config('pagination');
    $config = $this->config->item('pagination');
    $config['base_url'] = base_url('transactions/index');
    $config['total_rows'] = $total_rows;
    $config['per_page'] = $per_page;
    $config['uri_segment'] = 3;
    $config['use_page_numbers'] = TRUE;

    $query_string = trim($_SERVER['QUERY_STRING'] ?? '');
    if ($query_string !== '') {
        $config['suffix'] = '?' . $query_string;
        $config['first_url'] = $config['base_url'] . '/1?' . $query_string;
    }

    $this->pagination->initialize($config);
    $pagination_links = $this->pagination->create_links();

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
    public function store()
{
    $this->require_login();

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'داده‌ای دریافت نشد'
            ]));
    }

    // اعتبارسنجی ساده
    if (
        empty($data['type']) ||
        empty($data['category_id']) ||
        empty($data['amount']) ||
        empty($data['transaction_date'])
    ) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'همه فیلدهای ضروری را پر کنید'
            ]));
    }

    // تبدیل تاریخ شمسی به میلادی
    $date = en_digits($data['transaction_date']);
    $t = explode('/', $date);
    $gregorian = jalali_to_gregorian($t[0], $t[1], $t[2], '-');

    $transaction = [
        'user_id'          => $this->user_id,
        'type'             => $data['type'],
        'category_id'      => $data['category_id'],
        'amount'           => $data['amount'],
        'transaction_date' => $gregorian,
        'payment_method'   => $data['payment_method'] ?? 'cash',
        'reference'        => $data['reference'] ?? null,
        'description'      => $data['description'] ?? null,
        'status'           => 'completed'
    ];

    if ($this->Transaction_model->create_transaction($transaction)) {

        $this->recalc_user_balance();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success'
            ]));
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => 'error',
            'message' => 'خطا در ذخیره تراکنش'
        ]));
}
public function update($id)
{
    $this->require_login();

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'داده‌ای دریافت نشد'
            ]));
    }

    // اعتبارسنجی
    if (
        empty($data['type']) ||
        empty($data['category_id']) ||
        empty($data['amount']) ||
        empty($data['transaction_date'])
    ) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'همه فیلدهای ضروری را پر کنید'
            ]));
    }

    // تاریخ
    $date = en_digits($data['transaction_date']);
    $t = explode('/', $date);
    $gregorian = jalali_to_gregorian($t[0], $t[1], $t[2], '-');

    $update = [
        'type'             => $data['type'],
        'category_id'      => $data['category_id'],
        'amount'           => $data['amount'],
        'transaction_date' => $gregorian,
        'payment_method'   => $data['payment_method'] ?? 'cash',
        'reference'        => $data['reference'] ?? null,
        'description'      => $data['description'] ?? null
    ];

    $this->Transaction_model->update($id, $update);
    $this->recalc_user_balance();

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => 'success'
        ]));
}
public function get($id)
{
    $this->require_login();

    $transaction = $this->Transaction_model->get_by_id($id);

    if (!$transaction || $transaction->user_id != $this->user_id) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'تراکنش یافت نشد'
            ]));
    }

    // تبدیل تاریخ میلادی به شمسی
    if (!empty($transaction->transaction_date)) {
        $transaction->transaction_date = jdate(
            'Y/m/d',
            strtotime($transaction->transaction_date)
        );
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($transaction));
}

public function api($id)
{
    $this->require_login();

    $t = $this->Transaction_model->get_by_id_and_user($id, $this->user_id);

    if (!$t) {
        return $this->output
            ->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode(['message' => 'یافت نشد']));
    }

    $data = [
        'id' => $t->id,
        'type' => $t->type,
        'category_id' => (string)$t->category_id, 
        'amount' => (int)$t->amount,
        'payment_method' => $t->payment_method,
        'reference' => $t->reference,
        'description' => $t->description,
        'transaction_date' => jdate('Y/m/d', strtotime($t->transaction_date)),
    ];

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($data));
}} 
