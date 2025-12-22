<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends MY_Model {
    
    protected $table = 'categories';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_by_user($user_id) {
        return $this->get_where(['user_id' => $user_id], 'name', 'ASC');
    }

    public function create($data) {
    return $this->db->insert('categories', $data);
}
    
    public function get_income_categories($user_id = null) {
        $this->db->where('type', 'income');
        $this->db->where('status', 'active');
        
        $this->db->group_start();
            $this->db->where('user_id', $user_id);
            $this->db->or_where('is_default', 1);
        $this->db->group_end();
        
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }
    
    public function get_expense_categories($user_id = null) {
        $this->db->where('type', 'expense');
        $this->db->where('status', 'active');
        
        $this->db->group_start();
            $this->db->where('user_id', $user_id);
            $this->db->or_where('is_default', 1);
        $this->db->group_end();
        
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }
    public function has_transactions($category_id) {
    $this->db->where('category_id', $category_id);
    return $this->db->count_all_results('transactions') > 0;
}
public function get_all_for_user($user_id = null, $type = null) {
        $this->db->select('id, name, type');
        $this->db->from($this->table);
        $this->db->where('status', 'active');

        if ($type) {
            $this->db->where('type', $type);
        }

        $this->db->group_start();
            $this->db->where('user_id', $user_id);
            $this->db->or_where('is_default', 1);
        $this->db->group_end();

        $this->db->order_by('type, name');
        return $this->db->get()->result_array();
    }
public function exists_by_user($user_id, $type, $name) {
    $this->db->where('type', $type);
    $this->db->where('name', $name);
    $this->db->group_start();
        $this->db->where('user_id', $user_id);
        $this->db->or_where('is_default', 1);
    $this->db->group_end();
    return $this->db->count_all_results('categories') > 0;
}
    
    // متدهای زیر از parent ارث‌بری شده‌اند:
    // get_all(), get_by_id(), create(), update(), delete(), count_all()
}