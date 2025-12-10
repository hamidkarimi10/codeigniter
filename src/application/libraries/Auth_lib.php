<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_lib {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('User_model');
        $this->CI->load->library('session');
    }

    /**
     * لاگین کاربر
     */
    public function login($email, $password) {
        $user = $this->CI->User_model->get_by_email($email);
        
        if ($user && password_verify($password, $user->password)) {
            if ($user->status == 'active') {
                $this->set_user_session($user);
                
                // بروزرسانی آخرین زمان لاگین
                $this->CI->User_model->update($user->id, [
                    'last_login' => date('Y-m-d H:i:s')
                ]);
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * ذخیره اطلاعات کاربر در session
     */
    public function set_user_session($user) {
        $user_data = [
            'user_id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'avatar' => $user->avatar,
            'balance' => $user->balance,
            'logged_in' => true
        ];
        
        $this->CI->session->set_userdata($user_data);
    }

    /**
     * خروج کاربر
     */
    public function logout() {
        $this->CI->session->sess_destroy();
    }

    /**
     * بررسی لاگین بودن کاربر
     */
    public function is_logged_in() {
        return $this->CI->session->userdata('logged_in') === true;
    }

    /**
     * دریافت ID کاربر لاگین کرده
     */
    public function get_user_id() {
        return $this->CI->session->userdata('user_id');
    }

    /**}
     * دریافت اطلاعات کاربر لاگین کرده
     */
    public function get_user_data() {
        if (!$this->is_logged_in()) {
            return null;
        }
        
        return [
            'user_id' => $this->CI->session->userdata('user_id'),
            'email' => $this->CI->session->userdata('email'),
            'first_name' => $this->CI->session->userdata('first_name'),
            'last_name' => $this->CI->session->userdata('last_name'),
            'avatar' => $this->CI->session->userdata('avatar'),
            'balance' => $this->CI->session->userdata('balance'),
            'logged_in' => true
        ];
    }

    /**
     * بروزرسانی session پس از تغییر پروفایل
     */
    public function refresh_session() {
        if ($this->is_logged_in()) {
            $user_id = $this->get_user_id();
            $user = $this->CI->User_model->get_by_id($user_id);
            if ($user) {
                $this->set_user_session($user);
            }
        }
    }
}
?>