<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_users extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'first_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => FALSE,
            ),
            'last_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => FALSE,
            ),
            'email' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => FALSE,
            ),
            'password' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => FALSE,
            ),
            'phone' => array(
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => TRUE,
            ),
            'avatar' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => TRUE,
            ),
            'balance' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'status' => array(
                'type' => 'ENUM("active","inactive","suspended")',
                'default' => 'active',
            ),
            'email_verified' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ),
            'last_login' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
'created_at' => array(
    'type' => 'DATETIME',
    'null' => FALSE,
    'default' => '0000-00-00 00:00:00' // یا null اگر اجازه می‌دهی
),
'updated_at' => array(
    'type' => 'DATETIME',
    'null' => FALSE,
    'default' => '0000-00-00 00:00:00'
),
'softDeletes' => array(
    'type' => 'DATETIME',
    'null' => TRUE
)        ));
        
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users', TRUE);
        
        // ایجاد ایندکس
        $this->db->query('CREATE UNIQUE INDEX email_unique ON users(email)');
        
        // ایجاد کاربر ادمین پیش‌فرض
        $this->db->insert('users', array(
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'email_verified' => 1,
            'balance' => 1000.00,
            'status' => 'active'
        ));
    }

    public function down()
    {
        $this->dbforge->drop_table('users');
    }
}
?>