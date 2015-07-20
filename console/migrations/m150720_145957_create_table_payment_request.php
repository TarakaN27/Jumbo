<?php

use yii\db\Schema;
use yii\db\Migration;

class m150720_145957_create_table_payment_request extends Migration
{
    public function up()
    {
        /*
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%buser_invite_code}}', [
            'id' => Schema::TYPE_PK,
            'cuser_id' => Schema::TYPE_STRING,
            'is_unknown' => Schema::TYPE_STRING ,
            'user_name' => Schema::TYPE_SMALLINT ,
            'buser_id' => Schema::TYPE_INTEGER ,
            'pay_date' => Schema::TYPE_INTEGER . ' NOT NULL',
            'pay_summ' => Schema::TYPE_MONEY.' NOT NULL',
            'currency_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'legal_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'description' => Schema::TYPE_TEXT,
            'status' => Schema::TYPE_BOOLEAN ,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,

        ], $tableOptions);

        $this->createIndex('idx_buser_inv_code', '{{%buser_invite_code}}', 'code');
        $this->createIndex('idx_buser_inv_buser_id', '{{%buser_invite_code}}', 'buser_id');
        $this->createIndex('idx_buser_inv_email', '{{%buser_invite_code}}', 'email');
        */
    }

    public function down()
    {
        //$this->dropTable('{{%buser_invite_code}}');
    }

}
