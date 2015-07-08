<?php

use yii\db\Schema;
use yii\db\Migration;

class m150707_073204_create_table_payments extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%payments}}', [
            'id' => Schema::TYPE_PK,
            'cuser_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'pay_date' => Schema::TYPE_INTEGER . ' NOT NULL',
            'pay_summ' => Schema::TYPE_MONEY.' NOT NULL',
            'currency_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'service_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'legal_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'description' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        //индексы
        $this->createIndex('idx_pay_cuser_id', '{{%payments}}', 'cuser_id');
        $this->createIndex('idx_pay_service_id', '{{%payments}}', 'service_id');
        $this->createIndex('idx_pay_legal_id', '{{%payments}}', 'legal_id');

        //ключи
        $this->addForeignKey("contractor", "{{%payments}}", "cuser_id", "{{%c_user}}", "id", 'RESTRICT');
        $this->addForeignKey("legal_person", "{{%payments}}", "legal_id", "{{%legal_person}}", "id", 'RESTRICT');
        $this->addForeignKey("service", "{{%payments}}", "service_id", "{{%services}}", "id", 'RESTRICT');
        $this->addForeignKey("currency", "{{%payments}}", "currency_id", "{{%exchange_rates}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%payments}}');
    }
}
