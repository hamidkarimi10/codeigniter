<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model {
    
    protected $table = ''; // باید در کلاس فرزند تعریف شود
    protected $primary_key = 'id';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * دریافت تمام رکوردها
     */
    public function get_all($order_by = null, $order_dir = 'ASC') {
        if ($order_by) {
            $this->db->order_by($order_by, $order_dir);
        }
        return $this->db->get($this->table)->result();
    }
    
    /**
     * دریافت رکورد با ID
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, [$this->primary_key => $id])->row();
    }
    
    /**
     * دریافت رکوردها با شرط
     */
    public function get_where($where, $order_by = null, $order_dir = 'ASC') {
        if ($order_by) {
            $this->db->order_by($order_by, $order_dir);
        }
        return $this->db->get_where($this->table, $where)->result();
    }
    
    /**
     * دریافت یک رکورد با شرط
     */
    public function get_one_where($where) {
        return $this->db->get_where($this->table, $where)->row();
    }
    
    /**
     * ایجاد رکورد جدید
     */
    public function create($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * بروزرسانی رکورد
     */
    public function update($id, $data) {
        $this->db->where($this->primary_key, $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * حذف رکورد
     */
    public function delete($id) {
        $this->db->where($this->primary_key, $id);
        return $this->db->delete($this->table);
    }
    
    /**
     * حذف رکورد با شرط
     */
    public function delete_where($where) {
        return $this->db->delete($this->table, $where);
    }
    
    /**
     * شمارش تمام رکوردها
     */
    public function count_all() {
        return $this->db->count_all($this->table);
    }
    
    /**
     * شمارش رکوردها با شرط
     */
    public function count_where($where) {
        return $this->db->where($where)->count_all_results($this->table);
    }
    
    /**
     * بررسی وجود رکورد
     */
    public function exists($where) {
        return $this->db->where($where)->count_all_results($this->table) > 0;
    }
    
    /**
     * دریافت ستون خاص
     */
    public function get_column($column, $where = null) {
        $this->db->select($column);
        if ($where) {
            $this->db->where($where);
        }
        $result = $this->db->get($this->table)->result();
        
        return array_column($result, $column);
    }
}