<?php

use yii\db\Migration;

class m160414_112017_create_table_partner_purse_history extends Migration
{
    /***
     *
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_purse_history}}', [
            'id' => $this->primaryKey(),
            'cuser_id' => $this->integer()->notNull(),
            'amount' => $this->money(17,4)->notNull(),
            'type' => $this->smallInteger(),
            'payment_id' => $this->integer(),
            'expense_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-pphicuserid','{{%partner_purse_history}}','cuser_id');
        $this->createIndex('idx-pphipayid','{{%partner_purse_history}}','payment_id');
        $this->createIndex('idx-pphiexpenceid','{{%partner_purse_history}}','expense_id');

        $this->addForeignKey('FK_pphi_cuser_id','{{%partner_purse_history}}','cuser_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_pphi_payment_id','{{%partner_purse_history}}','payment_id','{{%payments}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_pphi_expense_id','{{%partner_purse_history}}','expense_id','{{%expense}}','id','CASCADE','RESTRICT');
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropTable('{{%partner_purse_history}}');
    }
}
