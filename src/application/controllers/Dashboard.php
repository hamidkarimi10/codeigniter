<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['Transaction_model', 'User_model']);

    }
    
public function index() {
    $this->require_login();
    
    $financial_summary = $this->Transaction_model->get_financial_summary($this->user_id);
    $recent_transactions = $this->Transaction_model->get_recent_transactions($this->user_id, 5);
    $chart_data = $this->Transaction_model->get_monthly_chart_data_filled($this->user_id, 6);
    $user_transactions = $this->Transaction_model->get_by_user($this->user_id);
    
    $data = [
        'page_title' => 'داشبورد مدیریت مالی',
        'user' => $this->user,
        'financial_summary' => $financial_summary,
        'recent_transactions' => $recent_transactions,
        'monthly_data' => json_encode($chart_data, JSON_NUMERIC_CHECK),
        'transactions_count' => count($user_transactions),
        'content' => 'dashboard/index'
    ];
    
    // استفاده از متد جدید
    $this->load->view('layout', $data);
}
}