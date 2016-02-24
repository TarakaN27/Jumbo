<?php

use yii\db\Migration;

class m160224_113927_create_table_enrollments extends Migration
{
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%enrolls}}', [
            'id' => $this->primaryKey(),
            'amount' => $this->money(),
            'repay' => $this->money(),
            'enroll' => $this->money(),
            'enr_req_id' => $this->integer(),
            'service_id' => $this->integer(),
            'cuser_id' => $this->integer(),
            'buser_id' => $this->integer(),
            'description' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);

        $this->addForeignKey('FK_enrls_req_id','{{%enrolls}}','enr_req_id','{{%enrollment_request}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_enrls_cuser_id','{{%enrolls}}','cuser_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_enrls_buser_id','{{%enrolls}}','buser_id','{{%b_user}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_enrls_serv_id','{{%enrolls}}','service_id','{{%services}}','id','SET NULL','RESTRICT');

        $this->addColumn('{{%enrollment_request}}','status',$this->smallInteger());
    }

    public function safeDown()
    {
        $this->dropTable('{{%enrolls}}');
        $this->dropColumn('{{%enrollment_request}}','status');
    }
}
