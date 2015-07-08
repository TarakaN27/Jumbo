<?php

use yii\db\Schema;
use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        //добавляем таблицу для бекэнд пользователей
        $this->createTable('{{%b_user}}', [
            'id' => Schema::TYPE_PK,
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'auth_key' => Schema::TYPE_STRING . '(32) DEFAULT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'password_reset_token' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING . ' NOT NULL',
            'role' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 5',
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        //индексы для оптимизации поиска
        $this->createIndex('idx_user_username', '{{%b_user}}', 'username');
        $this->createIndex('idx_user_email', '{{%b_user}}', 'email');
        $this->createIndex('idx_user_status', '{{%b_user}}', 'status');

        //добавляем таблицу для пользователей фронтенд
        $this->createTable('{{%c_user}}', [
            'id' => Schema::TYPE_PK,
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'ext_id' => Schema::TYPE_INTEGER,
            'type' => Schema::TYPE_INTEGER,
            'manager_id' => Schema::TYPE_INTEGER,
            'auth_key' => Schema::TYPE_STRING . '(32) DEFAULT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'password_reset_token' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING . ' NOT NULL',
            'role' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 5',
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        //индексы для оптимизации поиска
        $this->createIndex('idx_user_username', '{{%c_user}}', 'username');
        $this->createIndex('idx_user_email', '{{%c_user}}', 'email');
        $this->createIndex('idx_user_status', '{{%c_user}}', 'status');

    }

    public function down()
    {
        $this->dropTable('{{%b_user}}');
        $this->dropTable('{{%c_user}}');
    }
}
