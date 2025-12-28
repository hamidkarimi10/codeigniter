<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->helper('file');
    }
    
    // --- صفحه پروفایل (غیر API) ---
    public function profile() {
        $this->require_login();
        $data = [
            'page_title' => 'پروفایل کاربری',
            'user' => $this->user,
            'content' => 'user/profile'
        ];
        $this->load->view('layout', $data);
    }

    public function update() {
        $this->require_login();
        
        $this->form_validation->set_rules('first_name', 'نام', 'required|min_length[2]');
        $this->form_validation->set_rules('last_name', 'نام خانوادگی', 'required|min_length[2]');
        $this->form_validation->set_rules('email', 'ایمیل', 'required|valid_email');
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('user/profile');
            return;
        }
        
        $update_data = [
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email' => $this->input->post('email'),
        ];
        
        $password = $this->input->post('password');
        $password_confirm = $this->input->post('password_confirm');
        
        if (!empty($password)) {
            if ($password === $password_confirm) {
                $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $this->session->set_flashdata('error', 'تایید رمز عبور مطابقت ندارد.');
                redirect('user/profile');
                return;
            }
        }
        
        $old_avatar = $this->user->avatar;
        
        if (!empty($_FILES['avatar']['name'])) {
            $user_upload_dir = './uploads/avatars/user_' . $this->user_id . '/';
            if (!is_dir(FCPATH . 'uploads/avatars/user_' . $this->user_id)) {
                mkdir(FCPATH . 'uploads/avatars/user_' . $this->user_id, 0755, true);
            }

            $config['upload_path'] = $user_upload_dir;
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['file_name'] = 'avatar_' . time();
            $config['max_size'] = 2048;
            
            $this->load->library('upload', $config);
            
            if (!$this->upload->do_upload('avatar')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('user/profile');
                return;
            } else {
                $file_data = $this->upload->data();
                $update_data['avatar'] = 'uploads/avatars/user_' . $this->user_id . '/' . $file_data['file_name'];
            }
        }
        
        if ($this->User_model->update($this->user_id, $update_data)) {
            // 🗑️ پاک کردن آواتار قدیمی
            if ($old_avatar && strpos($old_avatar, 'default.png') === false) {
                $old_path = FCPATH . $old_avatar;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            $this->session->set_flashdata('success', 'اطلاعات با موفقیت بروزرسانی شد.');
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'خطا در بروزرسانی اطلاعات.');
            redirect('user/profile');
        }
    }


    public function api_profile() {
        $this->require_login();
        echo json_encode([
            'id' => $this->user->id,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'email' => $this->user->email,
            'avatar' => $this->user->avatar 
                ? base_url($this->user->avatar) 
                : base_url('uploads/avatars/default.png')
        ]);
    }

    public function api_update() {
        $this->require_login();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $errors = [];

        if (empty($input['first_name']) || strlen($input['first_name']) < 3) {
            $errors['first_name'] = 'نام باید حداقل 3 کاراکتر باشد.';
        }
        if (empty($input['last_name']) || strlen($input['last_name']) < 3) {
            $errors['last_name'] = 'نام خانوادگی باید حداقل 3 کاراکتر باشد.';
        }
        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'ایمیل معتبر نیست.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        $update_data = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
        ];

        if (!empty($input['password'])) {
            if ($input['password'] !== $input['password_confirm']) {
                http_response_code(422);
                echo json_encode(['errors' => ['password' => 'رمز عبور و تکرار آن یکسان نیستند.']]);
                return;
            }
            $update_data['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if ($this->User_model->update($this->user_id, $update_data)) {
            echo json_encode(['status' => 'success', 'message' => 'پروفایل با موفقیت به‌روزرسانی شد.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'خطا در به‌روزرسانی.']);
        }
    }

    public function api_upload_avatar() {
        $this->require_login();
        header('Content-Type: application/json');

        if (empty($_FILES['avatar'])) {
            http_response_code(400);
            echo json_encode(['message' => 'فایلی ارسال نشده است.']);
            return;
        }

        $user_upload_dir = './uploads/avatars/user_' . $this->user_id . '/';
        if (!is_dir(FCPATH . 'uploads/avatars/user_' . $this->user_id)) {
            mkdir(FCPATH . 'uploads/avatars/user_' . $this->user_id, 0755, true);
        }

        $old_avatar = $this->user->avatar;

        $config['upload_path'] = $user_upload_dir;
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['file_name'] = 'avatar_' . time();
        $config['max_size'] = 1024; // 1MB

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('avatar')) {
            http_response_code(422);
            echo json_encode(['message' => $this->upload->display_errors('', '')]);
            return;
        }

        $file_data = $this->upload->data();
        $avatar_path = 'uploads/avatars/user_' . $this->user_id . '/' . $file_data['file_name'];

        if ($this->User_model->update($this->user_id, ['avatar' => $avatar_path])) {
            if ($old_avatar && strpos($old_avatar, 'default.png') === false) {
                $old_path = FCPATH . $old_avatar;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'عکس پروفایل با موفقیت آپلود شد.',
                'avatar_url' => base_url($avatar_path)
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'خطا در ذخیره‌سازی.']);
        }
    }

    public function api_remove_avatar() {
        $this->require_login();
        header('Content-Type: application/json');

        if ($this->user->avatar && strpos($this->user->avatar, 'default.png') === false) {
            $old_path = FCPATH . $this->user->avatar;
            if (file_exists($old_path)) {
                unlink($old_path);
            }
        }

        if ($this->User_model->update($this->user_id, ['avatar' => null])) {
            echo json_encode([
                'status' => 'success',
                'message' => 'عکس پروفایل حذف شد.',
                'avatar_url' => base_url('uploads/avatars/default.png')
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'خطا در حذف.']);
        }
    }
}