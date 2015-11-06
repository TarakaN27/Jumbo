<?php

use yii\db\Schema;
use yii\db\Migration;

class m151105_080923_create_table_partner_conditions extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_condition}}', [
            'id' => Schema::TYPE_PK,
            'min_amount' => Schema::TYPE_INTEGER . ' NOT NULL',
            'max_amount' => Schema::TYPE_INTEGER . ' NOT NULL',
            'percent' => Schema::TYPE_DOUBLE . ' NOT NULL',
            'start_date' => Schema::TYPE_DATE . ' NOT NULL',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        //индексы для оптимизации поиска
        $this->createIndex('idx_min_amount', '{{%partner_condition}}', 'min_amount');
        $this->createIndex('idx_max_amount', '{{%partner_condition}}', 'max_amount');
        $this->createIndex('idx_start_date', '{{%partner_condition}}', 'start_date');
    }

    public function down()
    {
        $this->dropTable('{{%partner_condition}}');
    }
}
