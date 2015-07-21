<?php

use yii\db\Schema;
use yii\db\Migration;

class m150720_145957_create_table_payment_request extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%payment_request}}', [
            'id' => Schema::TYPE_PK,
            'cntr_id' => Schema::TYPE_INTEGER,
            'manager_id' => Schema::TYPE_INTEGER ,
            'owner_id' => Schema::TYPE_INTEGER ,
            'is_unknown' => Schema::TYPE_BOOLEAN ,
            'user_name' => Schema::TYPE_STRING ,
            'pay_date' => Schema::TYPE_INTEGER . ' NOT NULL',
            'pay_summ' => Schema::TYPE_MONEY . ' NOT NULL',
            'currency_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'legal_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'description' => Schema::TYPE_TEXT,
            'dialog_id' => Schema::TYPE_INTEGER,
            'status' => Schema::TYPE_BOOLEAN ,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,

        ], $tableOptions);

        $this->createIndex('idx_payment_request_cuser_id', '{{%payment_request}}', 'cntr_id');
        $this->createIndex('idx_payment_request_buser_id', '{{%payment_request}}', 'manager_id');
        $this->createIndex('idx_payment_request_owner_id', '{{%payment_request}}', 'owner_id');
        $this->createIndex('idx_payment_request_dialog_id', '{{%payment_request}}', 'dialog_id');
    }

    public function down()
    {
        $this->dropTable('{{%payment_request}}');
    }

}
