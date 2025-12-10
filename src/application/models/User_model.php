<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model {
    
    protected $table = 'users';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function get_by_email($email) {
        return $this->get_one_where(['email' => $email]);
    }
    
    public function get_by_token($token) {
        return $this->get_one_where(['remember_token' => $token]);
    }

    public function update_remember_token($user_id, $token) {
        return $this->update($user_id, ['remember_token' => $token]);
    }
    
    public function get_user_categories($user_id) {
        if (!isset($this->Category_model)) {
            $this->load->model('Category_model');
        }
        return $this->Category_model->get_by_user($user_id);
    }
    
    // متدهای زیر از parent ارث‌بری شده‌اند:
    // get_all(), get_by_id(), create(), update(), delete(), count_all()
}