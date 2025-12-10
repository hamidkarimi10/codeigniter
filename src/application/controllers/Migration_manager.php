<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_manager extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // فقط در محیط توسعه اجازه دسترسی بدهید
        if (ENVIRONMENT === 'production') {
            show_error('This controller is disabled in production environment.', 403);
        }
        
        // نیاز به تأیید امنیتی
        $this->check_access();
        
        $this->load->library('migration');
        $this->load->dbforge();
    }
    
    private function check_access() {
        // IP شما و IPهای مجاز دیگر
        $allowed_ips = [
            '127.0.0.1',        // localhost
            '::1',              // localhost IPv6
            'localhost',
            '172.19.0.1',       // ← IP شما
            '172.19.0.3 '        // ← Server IP
        ];
        
        $current_ip = $_SERVER['REMOTE_ADDR'];
        
        // اگر IP در لیست مجاز نیست
        if (!in_array($current_ip, $allowed_ips)) {
            // پیام خطای مفیدتر
            $message = "Access denied. IP not allowed.<br>";
            $message .= "Your IP: $current_ip<br>";
            $message .= "Allowed IPs: " . implode(', ', $allowed_ips) . "<br>";
            $message .= "<a href='" . site_url('migration_manager/bypass') . "'>Try bypass (temporary)</a>";
            
            show_error($message, 403);
        }
    }
    
    /**
     * صفحه بایپس موقت (برای وقتی که IP تغییر می‌کند)
     */
    public function bypass() {
        // ذخیره در سشن که کاربر دسترسی دارد
        $this->session->set_userdata('migration_access', true);
        
        redirect('migration_manager');
    }
    
    /**
     * صفحه اصلی
     */
    public function index() {
        // بررسی سشن برای دسترسی بایپس
        if (!$this->session->userdata('migration_access')) {
            $this->check_access();
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Migration Manager</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
                .warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .btn-success { background: #4CAF50; color: white; }
                .btn-danger { background: #f44336; color: white; }
                .btn-info { background: #2196F3; color: white; }
                .btn-warning { background: #ff9800; color: white; }
                .info-box { background: #e7f3fe; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>📊 Migration Manager</h1>
                
                <div class='info-box'>
                    <strong>Environment:</strong> " . ENVIRONMENT . "<br>
                    <strong>Your IP:</strong> " . $_SERVER['REMOTE_ADDR'] . "<br>
                    <strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "
                </div>
                
                <div class='warning'>
                    ⚠️ <strong>Warning:</strong> Use with caution! All actions are destructive.
                </div>
                
                <h2>Safe Actions</h2>
                <a href='" . site_url('migration_manager/status') . "' class='btn btn-info'>📈 View Status</a>
                <a href='" . site_url('migration_manager/run_all') . "' class='btn btn-success'>🚀 Run All Migrations</a>
                
                <h2>Dangerous Actions</h2>
                <div class='danger'>
                    ⚠️ <strong>DANGER:</strong> These actions will DELETE DATA!
                </div>
                <a href='" . site_url('migration_manager/rollback_all') . "' class='btn btn-warning' onclick='return confirm(\"⚠️ این کار تمام جداول بجز migrations را حذف می‌کند. ادامه؟\")'>↩️ Rollback All Tables</a>
                <a href='" . site_url('migration_manager/reset_database') . "' class='btn btn-danger' onclick='return confirm(\"🔥 این کار تمام جداول از جمله migrations را حذف و دوباره ایجاد می‌کند. همه داده‌ها از بین می‌روند! مطمئنید؟\")'>🔥 Reset Complete Database</a>
                
                <h2>Tools</h2>
                <a href='" . site_url('migration_manager/list_tables') . "' class='btn'>📋 List All Tables</a>
                <a href='" . site_url('migration_manager/check_foreign_keys') . "' class='btn'>🔗 Check Foreign Keys</a>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 12px;'>
                    Access granted for IP: " . $_SERVER['REMOTE_ADDR'] . " | 
                    <a href='" . site_url('migration_manager/logout') . "'>Logout</a>
                </div>
            </div>
            
            <script>
                // تایید مضاعف برای actions خطرناک
                document.querySelectorAll('.btn-danger, .btn-warning').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        if (!confirm('This is VERY dangerous! Are you ABSOLUTELY sure?')) {
                            e.preventDefault();
                        }
                    });
                });
            </script>
        </body>
        </html>";
    }
    
    /**
     * خروج از سیستم بایپس
     */
    public function logout() {
        $this->session->unset_userdata('migration_access');
        echo "<script>alert('Logged out successfully.'); window.location.href = '" . site_url() . "';</script>";
    }
    
    /**
     * اجرای تمام مایگریشن‌ها
     */
    public function run_all() {
        echo "<h1>🚀 Running All Migrations</h1>";
        
        $start_time = microtime(true);
        
        if ($this->migration->current() === FALSE) {
            $error = $this->migration->error_string();
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;'>
                    <h3>❌ Error</h3>
                    <pre>$error</pre>
                  </div>";
        } else {
            $end_time = microtime(true);
            $execution_time = round($end_time - $start_time, 2);
            
            echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 5px;'>
                    <h3>✅ Success!</h3>
                    <p>All migrations completed in $execution_time seconds.</p>
                  </div>";
            
            // نمایش جدول‌ها
            $this->list_tables();
        }
        
        echo "<br><a href='" . site_url('migration_manager') . "' class='btn btn-info'>← Back to Manager</a>";
    }
    
    /**
     * رول‌بک تمام جدول‌ها
     */
    public function rollback_all() {
        echo "<h1>↩️ Rolling Back All Tables</h1>";
        echo "<div class='danger'>⚠️ این عمل تمام جدول‌ها (بجز migrations) را حذف می‌کند!</div>";
        
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        
        $tables = $this->db->list_tables();
        $dropped = [];
        
        foreach ($tables as $table) {
            if ($table !== 'migrations') {
                if ($this->dbforge->drop_table($table, TRUE)) {
                    $dropped[] = $table;
                    echo "✅ Dropped: $table<br>";
                } else {
                    echo "⚠️ Failed to drop: $table<br>";
                }
            }
        }
        
        $this->db->truncate('migrations');
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0;'>
                <h3>✅ Completed!</h3>
                <p>Dropped " . count($dropped) . " tables.</p>
              </div>";
        
        echo "<a href='" . site_url('migration_manager/run_all') . "' class='btn btn-success'>🚀 Run Migrations Again</a> ";
        echo "<a href='" . site_url('migration_manager') . "' class='btn btn-info'>← Back to Manager</a>";
    }
    
    /**
     * ریست کامل دیتابیس
     */
    public function reset_database() {
        echo "<h1>🔥 Reset Complete Database</h1>";
        echo "<div class='danger'>🔥 این عمل تمام جدول‌ها را حذف و دوباره ایجاد می‌کند!</div>";
        
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        
        // حذف تمام جدول‌ها
        $tables = $this->db->list_tables();
        $dropped = [];
        
        foreach ($tables as $table) {
            $this->dbforge->drop_table($table, TRUE);
            $dropped[] = $table;
            echo "🔥 Dropped: $table<br>";
        }
        
        // ایجاد مجدد جدول migrations
        $this->db->query('CREATE TABLE IF NOT EXISTS `migrations` (
            `version` bigint(20) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "<h3>🔄 Running migrations...</h3>";
        
        if ($this->migration->current() === FALSE) {
            echo "<div class='danger'>❌ Error: " . $this->migration->error_string() . "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px;'>
                    <h3>✅ Database Reset Complete!</h3>
                  </div>";
            
            $this->list_tables();
        }
        
        echo "<br><a href='" . site_url('migration_manager') . "' class='btn btn-info'>← Back to Manager</a>";
    }
    
    /**
     * لیست تمام جدول‌ها
     */
    public function list_tables() {
        $tables = $this->db->list_tables();
        
        echo "<h2>📋 Database Tables (" . count($tables) . ")</h2>";
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <tr style='background: #f2f2f2;'>
                    <th>Table Name</th>
                    <th>Records</th>
                    <th>Size</th>
                </tr>";
        
        foreach ($tables as $table) {
            $count = $this->db->count_all($table);
            
            // تخمین حجم (ساده)
            $size = '-';
            if ($count > 0) {
                $size = number_format($count) . ' rows';
            }
            
            echo "<tr>
                    <td><strong>$table</strong></td>
                    <td>$count</td>
                    <td>$size</td>
                  </tr>";
        }
        
        echo "</table>";
    }
    
    /**
     * وضعیت مایگریشن
     */
    public function status() {
        $current_version = $this->migration->current_version();
        
        echo "<h1>📊 Migration Status</h1>";
        echo "<div class='info-box'>
                <strong>Current Version:</strong> $current_version<br>
                <strong>Database:</strong> " . $this->db->database . "
              </div>";
        
        $this->list_tables();
        
        echo "<br><a href='" . site_url('migration_manager') . "' class='btn btn-info'>← Back to Manager</a>";
    }
    
    /**
     * بررسی foreign keys
     */
    public function check_foreign_keys() {
        echo "<h1>🔗 Foreign Key Check</h1>";
        
        $result = $this->db->query("SELECT 
            TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, 
            REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = '" . $this->db->database . "' 
            AND REFERENCED_TABLE_NAME IS NOT NULL")->result_array();
        
        if (empty($result)) {
            echo "<p>No foreign keys found.</p>";
        } else {
            echo "<table border='1' cellpadding='10'>
                    <tr><th>Table</th><th>Column</th><th>References</th></tr>";
            
            foreach ($result as $row) {
                echo "<tr>
                        <td>{$row['TABLE_NAME']}</td>
                        <td>{$row['COLUMN_NAME']}</td>
                        <td>{$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}</td>
                      </tr>";
            }
            
            echo "</table>";
        }
        
        echo "<br><a href='" . site_url('migration_manager') . "' class='btn btn-info'>← Back to Manager</a>";
    }
}