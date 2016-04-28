<?php

use yii\db\Migration;

class m160428_114352_create_table_partner_bookkeeper_request extends Migration
{
    /**
     *
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_w_bookkeeper_request}}', [
            'id' => $this->primaryKey(),
            'buser_id' => $this->integer(),
            'partner_id' => $this->integer()->notNull(),
            'contractor_id' => $this->integer(),
            'amount' => $this->money(18,6),
            'currency_id' => $this->integer(),
            'legal_id' => $this->integer(),
            'request_id' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'status' => $this->smallInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-pwbr-buser_id','{{%partner_w_bookkeeper_request}}','buser_id');
        $this->createIndex('idx-pwbr-pid','{{%partner_w_bookkeeper_request}}','partner_id');
        $this->createIndex('idx-pwbr-cntr_id','{{%partner_w_bookkeeper_request}}','contractor_id');
        $this->createIndex('idx-pwbr-curr_id','{{%partner_w_bookkeeper_request}}','currency_id');
        $this->createIndex('idx-pwbr-legal_id','{{%partner_w_bookkeeper_request}}','legal_id');
        $this->createIndex('idx-pwbr-req_id','{{%partner_w_bookkeeper_request}}','request_id');
        $this->createIndex('idx-pwbr-crt_by','{{%partner_w_bookkeeper_request}}','created_by');

        $this->addForeignKey('FK-pwbr-buser_id','{{%partner_w_bookkeeper_request}}','buser_id','{{%b_user}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK-pwbr-partner_id','{{%partner_w_bookkeeper_request}}','partner_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwbr-cntr_id','{{%partner_w_bookkeeper_request}}','contractor_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwbr-curr_id','{{%partner_w_bookkeeper_request}}','currency_id','{{%exchange_rates}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwbr-legal_id','{{%partner_w_bookkeeper_request}}','legal_id','{{%legal_person}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwbr-req_id','{{%partner_w_bookkeeper_request}}','request_id','{{%partner_withdrawal_request}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwbr-crt_by','{{%partner_w_bookkeeper_request}}','created_by','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropTable('table_partner_bookkeeper_request');
    }
}
