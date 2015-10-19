<?php

use yii\db\Schema;
use yii\db\Migration;

class m151019_121024_create_table_promised_payment extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%promised_payment}}',[
            'id' => Schema::TYPE_PK,
            'cuser_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'buser_id_p' => Schema::TYPE_INTEGER,
            'amount' => Schema::TYPE_STRING.' NOT NULL',
            'paid_date' => Schema::TYPE_INTEGER.' NOT NULL',
            'paid' => Schema::TYPE_BOOLEAN.' NOT NULL ',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);

        $this->createIndex("idx_pp_cuser_id", "{{%promised_payment}}", "cuser_id");
    }

    public function down()
    {
        $this->dropTable('{{%promised_payment}}');
    }

}
