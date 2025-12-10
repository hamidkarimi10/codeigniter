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
    
    // متدهای زیر از parent ارث‌بری شده‌اند:
    // get_all(), get_by_id(), create(), update(), delete(), count_all()
}