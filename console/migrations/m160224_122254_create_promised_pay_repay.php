<?php

use yii\db\Migration;

class m160224_122254_create_promised_pay_repay extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%promised_pay_repay}}', [
            'id' => $this->primaryKey(),
            'amount' => $this->money(),
            'pr_pay_id' => $this->integer(),
            'payment_id' => $this->integer(),
            'enroll_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);


        $this->addForeignKey('FK_pprep_pr_pay_id','{{%promised_pay_repay}}','pr_pay_id','{{%promised_payment}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_pprep_payment_id','{{%promised_pay_repay}}','payment_id','{{%payments}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_pprep_enroll_id','{{%promised_pay_repay}}','enroll_id','{{%enrolls}}','id','CASCADE','RESTRICT');

    }

    public function safeDown()
    {
        $this->dropTable('{{%promised_pay_repay}}');
    }
}
