<?php

use yii\db\Schema;
use yii\db\Migration;

class m151029_070146_create_table_acts_file extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%acts_file}}',[
            'id' => Schema::TYPE_PK,
            'act_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'name' => Schema::TYPE_STRING,
            'path' => Schema::TYPE_STRING,
            'sent' => Schema::TYPE_BOOLEAN .' DEFAULT 0',
            'is_default' => Schema::TYPE_BOOLEAN.' DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{acts_file}}');
    }
}
