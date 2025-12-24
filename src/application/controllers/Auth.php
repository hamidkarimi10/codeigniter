<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();


        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('User_model');
         $this->check_remember();

        if (
            $this->session->userdata('logged_in') &&
            $this->router->method !== 'logout'
        ) {
            redirect('dashboard');
        }

    }

    public function index()
    {
        $this->login();
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            $_POST = $data;
        }
        // $this->redirect_if_logged_in();
        if ($this->session->userdata('logged_in')) {
    redirect('dashboard');
}

        $data = [
            'page_title' => 'ورود به سیستم',
            'error' => null,
            'success' => $this->session->flashdata('success')
        ];

        if ($this->input->post()) {
            $this->form_validation->set_rules('email', 'ایمیل', 'required|valid_email');
            $this->form_validation->set_rules('password', 'رمز عبور', 'required');

            if ($this->form_validation->run()) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');

                $user = $this->User_model->get_by_email($email);

                if ($user && password_verify($password, $user->password)) {

                    $this->session->set_userdata([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'logged_in' => true
                    ]);

                    if ($this->input->post('remember_me')) {
                        $token = bin2hex(random_bytes(16));
                        set_cookie([
                            'name' => 'remember_token',
                            'value' =>  $token,
                            // 'expire' => 30 * 24 * 60 * 60 ,
                            'expire' => 60,
                            'path' => '/',
                            'secure' => false ,
                            'httponly' => true
                        ]);
                        $this->User_model->update_remember_token($user->id, $token);
                    }

                    return $this->output
                        ->set_status_header(200)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'success' => true,
                            'message' => 'ورود موفق'
                        ]));

                } else {

                    return $this->output
                        ->set_status_header(401)
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            'success' => false,
                            'message' => 'ایمیل یا رمز عبور اشتباه است'
                        ]));
                }
            }
        }

        $this->render('auth/login', $data);
    }

    public function register()
    {
        $isAjax = $this->input->is_ajax_request();

        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            $_POST = $data;
        }

        $this->redirect_if_logged_in();

        if ($this->input->post()) {
            $this->form_validation->set_rules('first_name', 'نام', 'required|min_length[2]');
            $this->form_validation->set_rules('last_name', 'نام خانوادگی', 'required|min_length[2]');
            $this->form_validation->set_rules('email', 'ایمیل', 'required|regex_match[/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/]');
            $this->form_validation->set_rules('password', 'رمز عبور', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'تکرار رمز عبور', 'required|matches[password]');

            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                if ($isAjax) {
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(422)
                        ->set_output(json_encode(['errors' => $errors]));
                } else {
                    $data['errors'] = $errors;
                }
            } else {
                $email = $this->input->post('email');
                if ($this->User_model->get_by_email($email)) {
                    if ($isAjax) {
                        return $this->output
                            ->set_content_type('application/json')
                            ->set_status_header(409)
                            ->set_output(json_encode(['message' => 'این ایمیل قبلاً ثبت نام شده است.']));
                    } else {
                        $data['error'] = 'این ایمیل قبلاً ثبت نام شده است.';
                    }
                } else {
                    $user_data = [
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'email' => $email,
                        'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    if ($this->User_model->create($user_data)) {
                        if ($isAjax) {
                            return $this->output
                                ->set_content_type('application/json')
                                ->set_status_header(200)
                                ->set_output(json_encode(['message' => 'ثبت‌نام با موفقیت انجام شد.']));
                        } else {
                            $this->session->set_flashdata('success', 'ثبت نام موفق بود. لطفاً وارد شوید.');
                            redirect('auth/login');
                        }
                    } else {
                        $errorMsg = 'خطا در ایجاد حساب کاربری.';
                        if ($isAjax) {
                            return $this->output
                                ->set_content_type('application/json')
                                ->set_status_header(500)
                                ->set_output(json_encode(['message' => $errorMsg]));
                        } else {
                            $data['error'] = $errorMsg;
                        }
                    }
                }
            }
        }

        if (!$isAjax) {
            $data['page_title'] = 'ثبت نام';
            $this->render('auth/register', $data);
        }
    }

    public function logout()
    {
        $user_id = $this->session->userdata('user_id');

    if ($user_id) {
        $this->User_model->update($user_id, ['remember_token' => null]);
    }

    delete_cookie('remember_token');

    unset($_COOKIE['remember_token']);

    $this->session->sess_destroy();

    redirect('auth/login');
    }
protected function check_remember() {
    
    if (!$this->session->userdata('user_id') && get_cookie('remember_token')) {
        $token = get_cookie('remember_token');
        $user = $this->User_model->get_by_token($token);

        if ($user) {
            $this->session->set_userdata([
                'user_id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'logged_in' => true
            ]);
        } else {
            delete_cookie('remember_token');
        }
    }
}

}