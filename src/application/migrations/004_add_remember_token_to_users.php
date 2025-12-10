<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_remember_token_to_users extends CI_Migration {

    public function up()
    {
        $fields = array(
            'remember_token' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => TRUE,
            )
        );

        $this->dbforge->add_column('users', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('users', 'remember_token');
    }
}
