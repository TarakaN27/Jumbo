<?php

use yii\db\Schema;
use yii\db\Migration;

class m151001_071518_create_table_external_acount extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cuser_external_account}}',[
            'id' => Schema::TYPE_PK,
            'type' => Schema::TYPE_INTEGER.' NOT NULL',
            'cuser_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'login' => Schema::TYPE_STRING.' NOT NULL ',
            'password' => Schema::TYPE_STRING.' NOT NULL ',
            'secret_key' => Schema::TYPE_STRING.' NOT NULL ',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);

        $this->addForeignKey("FK_cea_cuser_id", "{{%cuser_external_account}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%cuser_external_account}}');
    }
}
