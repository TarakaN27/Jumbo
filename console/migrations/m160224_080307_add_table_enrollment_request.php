<?php

use yii\db\Migration;

class m160224_080307_add_table_enrollment_request extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%enrollment_request}}', [
            'id' => $this->primaryKey(),
            'payment_id' => $this->integer(),
            'pr_payment_id' => $this->integer(),
            'service_id' => $this->integer()->notNull(),
            'assigned_id' => $this->integer(),
            'cuser_id' => $this->integer()->notNull(),
            'amount' => $this->money(),
            'pay_amount' => $this->money(),
            'pay_currency' => $this->integer(),
            'pay_date' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);

        $this->addForeignKey('FK_enr_payment_id','{{%enrollment_request}}','payment_id','{{%payments}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_enr_pr_payment_id','{{%enrollment_request}}','pr_payment_id','{{%promised_payment}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_enr_service_id','{{%enrollment_request}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_enr_assigned_id','{{%enrollment_request}}','assigned_id','{{%b_user}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_enr_cuser_id','{{%enrollment_request}}','cuser_id','{{%c_user}}','id','CASCADE','RESTRICT');
    }//

    public function down()
    {
        $this->dropTable('{{%enrollment_request}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
