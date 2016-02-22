<?php

use yii\db\Schema;
use yii\db\Migration;

class m150706_120553_table_create_exchange_rates extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%exchange_rates}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'code' => Schema::TYPE_STRING . ' NOT NULL',
            'nbrb' => Schema::TYPE_INTEGER.' NOT NULL',
            'cbr' => Schema::TYPE_INTEGER.' NOT NULL',
            'nbrb_rate' => Schema::TYPE_MONEY.' NOT NULL',
            'cbr_rate' => Schema::TYPE_MONEY.' NOT NULL',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%exchange_rates}}');
    }
}
