<?php

use yii\db\Schema;
use yii\db\Migration;

class m151104_085546_create_table_puser extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        //добавляем таблицу для бекэнд пользователей
        $this->createTable('{{%partner}}', [
            'id' => Schema::TYPE_PK,
            'fname' => Schema::TYPE_STRING ,
            'lname' => Schema::TYPE_STRING ,
            'mname' => Schema::TYPE_STRING ,
            'description' => Schema::TYPE_TEXT,
            'email' => Schema::TYPE_STRING,
            'phone' => Schema::TYPE_STRING,
            'post_address' => Schema::TYPE_TEXT,
            'ch_account' =>Schema::TYPE_TEXT,
            'psk' => Schema::TYPE_STRING,
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        //индексы для оптимизации поиска
        $this->createIndex('idx_part_psk', '{{%partner}}', 'psk');
    }

    public function down()
    {
        $this->dropTable('{{%partner}}');
    }
}
