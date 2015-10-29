<?php

use yii\db\Schema;
use yii\db\Migration;

class m151027_130830_create_table_acts extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%acts_template}}',[
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING,
            'path' => Schema::TYPE_STRING,
            'is_default' => Schema::TYPE_BOOLEAN,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);

        $this->createTable('{{%acts}}',[
            'id' => Schema::TYPE_PK,
            'cuser_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'buser_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'service_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'template_id' => Schema::TYPE_INTEGER.' NOT NULL ',
            'amount' => Schema::TYPE_STRING.' NOT NULL ',
            'act_date' => Schema::TYPE_DATE,
            'sent' => Schema::TYPE_BOOLEAN.' DEFAULT 0 ',
            'change' => Schema::TYPE_BOOLEAN.' DEFAULT 0 ',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);

        $this->createIndex("idx_acts_cuser_id", "{{%acts}}", "cuser_id");
        $this->createIndex("idx_acts_buser_id", "{{%acts}}", "buser_id");
        $this->createIndex("idx_acts_service_id", "{{%acts}}", "service_id");

        $this->addForeignKey("FK_acts_cuser_id", "{{%acts}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_acts_buser_id", "{{%acts}}", "buser_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_acts_service_id", "{{%acts}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_acts_template_id", "{{%acts}}", "template_id", "{{%acts_template}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{acts_template}}');
        $this->dropTable('{{%acts}}');
    }

}
