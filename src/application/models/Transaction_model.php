<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends MY_Model {

    protected $table = 'transactions';

    public function __construct() {
        parent::__construct();
    }

    // --- متدهای عمومی ---

    public function get_all($order_by = null, $order_dir = 'ASC') {
        if (!$order_by) {
            $order_by = 't.transaction_date';
            $order_dir = 'DESC';
        }

        return $this->get_with_category()
            ->order_by($order_by, $order_dir)
            ->get()->result();
    }

    public function get_by_user($user_id, $limit = null) {
        $query = $this->get_with_category()
            ->where('t.user_id', $user_id)
            ->order_by('t.transaction_date', 'DESC');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->result();
    }

    public function get_recent_transactions($user_id, $limit = 5) {
        return $this->get_by_user($user_id, $limit);
    }

    public function get_financial_summary($user_id) {
        $summary = new stdClass();

        $this->db->select_sum('amount');
        $this->db->where('user_id', $user_id);
        $this->db->where('type', 'income');
        $this->db->where('status', 'completed');
        $result = $this->db->get($this->table)->row();
        $summary->total_income = $result && $result->amount ? floatval($result->amount) : 0;

        $this->db->select_sum('amount');
        $this->db->where('user_id', $user_id);
        $this->db->where('type', 'expense');
        $this->db->where('status', 'completed');
        $result = $this->db->get($this->table)->row();
        $summary->total_expense = $result && $result->amount ? floatval($result->amount) : 0;

        $summary->balance = $summary->total_income - $summary->total_expense;
        return $summary;
    }
    public function create_transaction($data) {
        return $this->create($data);
    }

    public function get_transactions_count($user_id = null) {
        return $user_id ? $this->count_where(['user_id' => $user_id]) : $this->count_all();
    }

    public function delete_by_user($transaction_id, $user_id) {
        return $this->delete_where(['id' => $transaction_id, 'user_id' => $user_id]);
    }

    public function get_sum_by_type($user_id, $type) {
        $this->db->select_sum('amount');
        $this->db->where('user_id', $user_id);
        $this->db->where('type', $type);
        $this->db->where('status', 'completed');
        $row = $this->db->get($this->table)->row();
        return $row && $row->amount ? floatval($row->amount) : 0;
    }

    // --- متدهای جدید برای صفحه‌بندی ---

    /**
     * شمارش تعداد تراکنش‌های فیلتر شده
     */
public function count_by_filters($user_id, $filters) {
    $this->_apply_filters($user_id, $filters);
    return (int) $this->db->count_all_results();
}    /**
     * دریافت تراکنش‌های فیلتر شده با صفحه‌بندی
     */
    public function get_by_filters_paginated($user_id, $filters, $limit, $offset) {
        $this->db->select('t.*, c.name as category_name, c.color as category_color');
        $this->_apply_filters($user_id, $filters);
        return $this->db
            ->order_by('t.transaction_date', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * (اختیاری) برای سازگاری با کدهای قبلی — بدون pagination
     */
    public function get_by_filters($user_id, $filters) {
        return $this->get_by_filters_paginated($user_id, $filters, PHP_INT_MAX, 0);
    }

    // --- متدهای کمکی ---

    /**
     * اعمال فیلترهای مشترک روی Query Builder
     */
    private function _apply_filters($user_id, $filters) {
        $this->db->from('transactions t')
            ->join('categories c', 'c.id = t.category_id', 'left')
            ->where('t.user_id', $user_id);

        if (!empty($filters['from'])) {
            $this->db->where('t.transaction_date >=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $this->db->where('t.transaction_date <=', $filters['to']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('t.type', $filters['type']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('t.category_id', $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('t.description', $filters['search'])
                ->or_like('t.reference', $filters['search'])
            ->group_end();
        }

        return $this;
    }

    /**
     * Helper for queries with category join
     */
    private function get_with_category() {
        return $this->db->select('t.*, c.name as category_name, c.type as category_type, c.color as category_color')
            ->from($this->table . ' t')
            ->join('categories c', 't.category_id = c.id');
    }
    public function get_by_id_and_user($id, $user_id) {
        if (!$id || !$user_id) {
            return null;
        }

        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('transactions'); 

        return $query->row(); 
    }

    public function get_monthly_chart_data_filled($user_id, $months = 6) {
    // ۱. محاسبه ماه‌های مورد نظر (میلادی)
    $dates = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $dates[] = date('Y-m', strtotime("-$i months"));
    }

    // ۲. دریافت داده‌های واقعی از دیتابیس
    $start_date = $dates[0] . '-01';
    $end_date = date('Y-m-t'); // آخرین روز ماه جاری

    $query = $this->db->query("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
        FROM {$this->table}
        WHERE user_id = ? 
          AND transaction_date BETWEEN ? AND ?
          AND status = 'completed'
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
    ", [$user_id, $start_date, $end_date]);

    $db_data = [];
    foreach ($query->result() as $row) {
        $db_data[$row->month] = $row;
    }

    // ۳. پر کردن ماه‌های خالی با 0
    $result = [];
    foreach ($dates as $month) {
        if (isset($db_data[$month])) {
            $result[] = $db_data[$month];
        } else {
            $result[] = (object) [
                'month' => $month,
                'income' => 0,
                'expense' => 0
            ];
        }
    }

    return $result;
}

    // متدهای ارث‌بری‌شده از MY_Model:
    // get_by_id(), update(), delete(), create(), count_where(), etc.
}