<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_categories extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => FALSE,
            ),
            'description' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'type' => array(  
                'type' => "ENUM('income','expense')",
                'default' => 'expense',
                'null' => FALSE
            ),
            'color' => array(
                'type' => 'VARCHAR',
                'constraint' => '7',
                'default' => '#007bff',
            ),
            'icon' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => 'fa-folder',
            ),
            'parent_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => TRUE,
            ),
            'is_default' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ),
            'status' => array(
                'type' => "ENUM('active','inactive')",
                'default' => 'active',
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE,
                'default' => '0000-00-00 00:00:00'
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE,
                'default' => '0000-00-00 00:00:00'
            ),
            'deleted_at' => array( 
                'type' => 'DATETIME',
                'null' => TRUE
            )
        ));
        
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('categories', TRUE);
        
        // ایجاد ایندکس‌ها
        $this->db->query('CREATE INDEX user_id_index ON categories(user_id)');
        $this->db->query('CREATE INDEX type_index ON categories(type)');
        
        // $default_categories = array(
        //     // درآمدها
        //     array(
        //         'user_id' => 1,
        //         'name' => 'حقوق',
        //         'type' => 'income', 
        //         'color' => '#28a745',
        //         'icon' => 'fa-money-bill',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     ),
        //     array(
        //         'user_id' => 1,
        //         'name' => 'فروش',
        //         'type' => 'income',
        //         'color' => '#20c997',
        //         'icon' => 'fa-store',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     ),
        //     array(
        //         'user_id' => 1,
        //         'name' => 'سود سرمایه‌گذاری',
        //         'type' => 'income',
        //         'color' => '#17a2b8',
        //         'icon' => 'fa-chart-line',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     ),
            
        //     // هزینه‌ها
        //     array(
        //         'user_id' => 1,
        //         'name' => 'خوراک',
        //         'type' => 'expense',
        //         'color' => '#dc3545',
        //         'icon' => 'fa-utensils',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     ),
        //     array(
        //         'user_id' => 1,
        //         'name' => 'حمل و نقل',
        //         'type' => 'expense',
        //         'color' => '#6f42c1',
        //         'icon' => 'fa-car',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     ),
        //     array(
        //         'user_id' => 1,
        //         'name' => 'مسکن',
        //         'type' => 'expense',
        //         'color' => '#e83e8c',
        //         'icon' => 'fa-home',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     ),
        //     array(
        //         'user_id' => 1,
        //         'name' => 'پوشاک',
        //         'type' => 'expense',
        //         'color' => '#fd7e14',
        //         'icon' => 'fa-tshirt',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     ),
        //     array(
        //         'user_id' => 1,
        //         'name' => 'سلامت',
        //         'type' => 'expense',
        //         'color' => '#6c757d',
        //         'icon' => 'fa-heart',
        //         'is_default' => 1,
        //         'status' => 'active',
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s')
        //     )
        // );
        
        // // فقط اگر جدول خالی است داده‌ها را اضافه کن
        // if ($this->db->count_all('categories') == 0) {
        //     $this->db->insert_batch('categories', $default_categories);
        // }
    }

    public function down()
    {
        $this->dbforge->drop_table('categories');
    }
}