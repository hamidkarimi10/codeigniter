<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_transactions extends CI_Migration {

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
            'category_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE
            ),
            'amount' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => FALSE
            ),
            'type' => array(
                'type' => 'ENUM("income","expense")',
                'null' => FALSE
            ),
            'description' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'transaction_date' => array(
                'type' => 'DATE',
                'null' => FALSE
            ),
            'reference' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
            ),
            'payment_method' => array(
                'type' => 'ENUM("cash","card","transfer","check")',
                'default' => 'cash',
            ),
            'status' => array(
                'type' => 'ENUM("completed","pending","cancelled")',
                'default' => 'completed',
            ),
            'recurring' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ),
            'recurring_pattern' => array(
                'type' => 'ENUM("daily","weekly","monthly","yearly")',
                'null' => TRUE,
            ),
            'attachments' => array(
                'type' => 'TEXT',
                'null' => TRUE,
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
'softDeletes' => array(
    'type' => 'DATETIME',
    'null' => TRUE
)        ));
        
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('transactions', TRUE);
        
        // ایجاد ایندکس‌ها
        $this->db->query('CREATE INDEX user_id_index ON transactions(user_id)');
        $this->db->query('CREATE INDEX category_id_index ON transactions(category_id)');
        $this->db->query('CREATE INDEX type_index ON transactions(type)');
        $this->db->query('CREATE INDEX transaction_date_index ON transactions(transaction_date)');
        
        // ایجاد تراکنش‌های نمونه
        $sample_transactions = array(
            array(
                'user_id' => 1,
                'category_id' => 1,
                'amount' => 5000.00,
                'type' => 'income',
                'description' => 'حقوق ماهانه',
                'transaction_date' => date('Y-m-d'),
                'payment_method' => 'transfer',
                'status' => 'completed'
            ),
            array(
                'user_id' => 1,
                'category_id' => 4,
                'amount' => 150.50,
                'type' => 'expense',
                'description' => 'خرید مواد غذایی',
                'transaction_date' => date('Y-m-d'),
                'payment_method' => 'cash',
                'status' => 'completed'
            ),
            array(
                'user_id' => 1,
                'category_id' => 5,
                'amount' => 75.00,
                'type' => 'expense',
                'description' => 'هزینه تاکسی',
                'transaction_date' => date('Y-m-d'),
                'payment_method' => 'card',
                'status' => 'completed'
            )
        );
        
        $this->db->insert_batch('transactions', $sample_transactions);
    }

    public function down()
    {
        $this->dbforge->drop_table('transactions');
    }
}
?>