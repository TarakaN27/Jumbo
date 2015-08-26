<?php

use yii\db\Schema;
use yii\db\Migration;

class m150826_114504_create_table_cuser_prefer_cond extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cuser_prefer_pay_cond}}',[
            'id' => Schema::TYPE_PK,
            'cuser_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'service_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'cond_id' => Schema::TYPE_INTEGER.' NOT NULL ',
        ],$tableOptions);



        $this->addForeignKey("FK_cppc_cuser_id", "{{%cuser_prefer_pay_cond}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_cppc_cond_id", "{{%cuser_prefer_pay_cond}}", "cond_id", "{{%payment_condition}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_cppc_service_id", "{{%cuser_prefer_pay_cond}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%cuser_prefer_pay_cond}}');
    }

}
