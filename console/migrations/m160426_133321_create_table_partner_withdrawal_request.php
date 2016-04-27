<?php

use yii\db\Migration;

class m160426_133321_create_table_partner_withdrawal_request extends Migration
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

        $this->createTable('{{%partner_withdrawal_request}}', [
            'id' => $this->primaryKey(),
            'partner_id' => $this->integer()->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'amount' => $this->money(18,6),
            'currency_id' => $this->integer(),
            'manager_id' => $this->integer(),
            'created_by' => $this->integer(),
            'date' => $this->integer(),
            'status' => $this->smallInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-pwr-pid','{{%partner_withdrawal_request}}','partner_id');
        $this->createIndex('idx-pwr-manager_id','{{%partner_withdrawal_request}}','manager_id');
        $this->createIndex('idx-pwr-created_by','{{%partner_withdrawal_request}}','created_by');

        $this->addForeignKey('FK-pwr-legal_person','{{%partner_withdrawal_request}}','partner_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwr-serv_id','{{%partner_withdrawal_request}}','manager_id','{{%b_user}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK-pwr-exp_cat_id','{{%partner_withdrawal_request}}','created_by',"{{%b_user}}",'id','SET NULL','RESTRICT');
    }

    /**
     *
     */
    public function down()
    {
        $this->dropForeignKey('FK-pwr-legal_person','{{%partner_withdrawal_request}}');
        $this->dropForeignKey('FK-pwr-serv_id','{{%partner_withdrawal_request}}');
        $this->dropForeignKey('FK-pwr-exp_cat_id','{{%partner_withdrawal_request}}');

        $this->dropIndex('idx-pwr-pid','{{%partner_withdrawal_request}}');
        $this->dropIndex('idx-pwr-manager_id','{{%partner_withdrawal_request}}');
        $this->dropIndex('idx-pwr-created_by','{{%partner_withdrawal_request}}');

        $this->dropTable('{{%partner_withdrawal_request}}');
    }
}
