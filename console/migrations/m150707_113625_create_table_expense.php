<?php

use yii\db\Schema;
use yii\db\Migration;

class m150707_113625_create_table_expense extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%expense}}', [
            'id' => Schema::TYPE_PK,
            'pay_date' => Schema::TYPE_INTEGER . ' NOT NULL',
            'pay_summ' => Schema::TYPE_MONEY.' NOT NULL',
            'currency_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'legal_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'cuser_id' => Schema::TYPE_INTEGER,
            'cat_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'description' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        //индексы
        $this->createIndex('idx_expense_cuser_id', '{{%expense}}', 'cuser_id');
        $this->createIndex('idx_expense_cat_id', '{{%expense}}', 'cat_id');
        $this->createIndex('idx_expense_legal_id', '{{%expense}}', 'legal_id');

        //ключи
        $this->addForeignKey("cuser", "{{%expense}}", "cuser_id", "{{%c_user}}", "id", 'RESTRICT');
        $this->addForeignKey("legal", "{{%expense}}", "legal_id", "{{%legal_person}}", "id", 'RESTRICT');
        $this->addForeignKey("category", "{{%expense}}", "cat_id", "{{%expense_categories}}", "id", 'RESTRICT');
        $this->addForeignKey("curr", "{{%expense}}", "currency_id", "{{%exchange_rates}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%expense}}');
    }
}
