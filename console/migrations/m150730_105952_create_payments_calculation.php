<?php

use yii\db\Schema;
use yii\db\Migration;

class m150730_105952_create_payments_calculation extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%payments_calculations}}', [
            'id' => Schema::TYPE_PK,
            'payment_id' => Schema::TYPE_INTEGER,
            'pay_cond_id' => Schema::TYPE_INTEGER,
            'tax' =>  ' decimal (19,10) NOT NULL',
            'profit' => ' decimal (19,10) DEFAULT 0',
            'production' =>  ' decimal (19,10) NOT NULL',
            'cnd_corr_factor' =>  ' decimal (19,10) NOT NULL',
            'cnd_commission' => ' decimal (19,10) DEFAULT 0',
            'cnd_sale' =>  ' decimal (19,10) NOT NULL',
            'cnd_tax' => ' decimal (19,10) DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("FK_pc_payment_id", "{{%payments_calculations}}", "payment_id", "{{%payments}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_pc_pay_cond_id", "{{%payments_calculations}}", "pay_cond_id", "{{%payment_condition}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%payments_calculations}}');
    }

}
