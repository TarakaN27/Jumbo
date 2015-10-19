<?php

use yii\db\Schema;
use yii\db\Migration;

class m151019_131250_add_colomn_to_cuser extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cuser_settings}}',[
            'id' => Schema::TYPE_PK,
            'cuser_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'pp_max' => Schema::TYPE_INTEGER,   // сумма максимального обещенного платежа
            'pp_percent' => Schema::TYPE_INTEGER, // процент для обещенного платежа
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);

        $this->createIndex("idx_cs_cuser_id", "{{%cuser_settings}}", "cuser_id");
        $this->addForeignKey("cs_fk_cuser", "{{%cuser_settings}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%cuser_settings}}');
    }

}
