<?php

use yii\db\Schema;
use yii\db\Migration;

class m150819_085401_create_bill_template extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bill_template}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING. ' NOT NULL ',
            'l_person_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'service_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'object_text' => Schema::TYPE_TEXT.' NOT NULL ',
            'description' => Schema::TYPE_TEXT,
            'use_vat' => Schema::TYPE_BOOLEAN. ' DEFAULT 0',
            'vat_rate' => Schema::TYPE_MONEY,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("FK_bt_l_person_id", "{{%bill_template}}", "l_person_id", "{{%legal_person}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_bt_service_id", "{{%bill_template}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%bill_template}}');
    }

}
