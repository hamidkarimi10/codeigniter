<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_data extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // فقط در محیط توسعه اجازه دسترسی
        if (ENVIRONMENT !== 'development') {
            show_error('Only allowed in development environment', 403);
        }
    }
    
    public function generate() {
        echo "<h2>در حال ایجاد داده‌های تستی...</h2>";
        
        $this->load->model(['Transaction_model', 'Category_model', 'User_model']);
        
        // گرفتن کاربر اول
        $user = $this->User_model->get_by_id(1);
        if (!$user) {
            echo "کاربری یافت نشد! ابتدا ثبت نام کنید.";
            return;
        }
        
        $user_id = $user->id;
        
        // دریافت دسته‌بندی‌های کاربر
        $income_categories = $this->Category_model->get_income_categories($user_id);
        $expense_categories = $this->Category_model->get_expense_categories($user_id);
        
        if (empty($income_categories) || empty($expense_categories)) {
            echo "دسته‌بندی‌ها یافت نشدند! ابتدا دسته‌بندی ایجاد کنید.";
            return;
        }
        
        // حلقه برای ایجاد 20 تراکنش تستی
        for ($i = 1; $i <= 20; $i++) {
            // تصمیم‌گیری تصادفی: درآمد یا هزینه
            $is_income = rand(0, 1);
            
            if ($is_income) {
                $type = 'income';
                $category = $income_categories[array_rand($income_categories)];
                $amount = rand(100000, 5000000); // 100,000 تا 5,000,000 تومان
            } else {
                $type = 'expense';
                $category = $expense_categories[array_rand($expense_categories)];
                $amount = rand(50000, 2000000); // 50,000 تا 2,000,000 تومان
            }
            
            // تاریخ تصادفی در 6 ماه اخیر
            $days_ago = rand(0, 180); // 0 تا 180 روز گذشته
            $transaction_date = date('Y-m-d', strtotime("-$days_ago days"));
            
            // توضیحات مختلف
            $descriptions = [
                'فروش محصول',
                'حقوق ماهیانه',
                'خرید مواد غذایی',
                'هزینه حمل و نقل',
                'پرداخت قبوض',
                'خرید پوشاک',
                'دریافت وام',
                'هزینه مسکن',
                'درآمد فروشگاه',
                'خرید لوازم منزل'
            ];
            
            $description = $descriptions[array_rand($descriptions)];
            
            // داده تراکنش
            $transaction_data = [
                'user_id' => $user_id,
                'category_id' => $category->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $description . " #" . $i,
                'transaction_date' => $transaction_date,
                'payment_method' => ['cash', 'card', 'online'][rand(0, 2)],
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // ذخیره در دیتابیس
            $transaction_id = $this->Transaction_model->create($transaction_data);
            
            if ($transaction_id) {
                echo "✅ تراکنش {$i} ایجاد شد: {$description} - {$amount} تومان ({$type})<br>";
            } else {
                echo "❌ خطا در ایجاد تراکنش {$i}<br>";
            }
        }
        
        // بروزرسانی موجودی کاربر
        $summary = $this->Transaction_model->get_financial_summary($user_id);
        $this->User_model->update($user_id, ['balance' => $summary->balance]);
        
        echo "<hr><h3>✅ داده‌های تستی با موفقیت ایجاد شدند!</h3>";
        echo "<p>تعداد تراکنش‌های ایجاد شده: 20</p>";
        echo "<p><a href='" . site_url('dashboard') . "'>مشاهده داشبورد</a></p>";
    }
    
    public function clear() {
        echo "<h2>حذف تمام تراکنش‌های تستی</h2>";
        
        $this->load->model('Transaction_model');
        
        // حذف همه تراکنش‌های کاربر 1
        $deleted = $this->db->where('user_id', 1)->delete('transactions');
        
        // بازنشانی موجودی کاربر
        $this->db->where('id', 1)->update('users', ['balance' => 0]);
        
        echo "<p>✅ {$deleted} تراکنش حذف شدند.</p>";
        echo "<p><a href='" . site_url('dashboard') . "'>بازگشت به داشبورد</a></p>";
    }
}