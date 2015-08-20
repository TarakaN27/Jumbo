<?php

use yii\db\Schema;
use yii\db\Migration;

class m150819_121849_create_bills_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bill_docx_template}}',[
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING.' NOT NULL',
            'src' => Schema::TYPE_STRING.' NOT NULL ',
            'is_default' => Schema::TYPE_BOOLEAN.' DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);

        $this->createTable('{{%bills}}', [
            'id' => Schema::TYPE_PK,
            'manager_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'cuser_id' =>Schema::TYPE_INTEGER.' NOT NULL',
            'l_person_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'service_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'docx_tmpl_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'amount' => Schema::TYPE_INTEGER.' NOT NULL ',
            'bill_number' => Schema::TYPE_INTEGER,
            'bill_date' => Schema::TYPE_DATE,
            'bill_template' => Schema::TYPE_INTEGER,
            'use_vat' => Schema::TYPE_BOOLEAN,
            'vat_rate' => Schema::TYPE_MONEY,
            'description' => Schema::TYPE_TEXT,
            'object_text' => Schema::TYPE_TEXT.' NOT NULL ',
            'buy_target' => Schema::TYPE_STRING.' NOT NULL ',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("FK_bls_manager_id", "{{%bills}}", "manager_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_bls_docx_tmpl_id", "{{%bills}}", "docx_tmpl_id", "{{%bill_docx_template}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_bls_l_person_id", "{{%bills}}", "l_person_id", "{{%legal_person}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_bls_service_id", "{{%bills}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%bill_docx_template}}');
        $this->dropTable('{{%bills}}');
    }


}
