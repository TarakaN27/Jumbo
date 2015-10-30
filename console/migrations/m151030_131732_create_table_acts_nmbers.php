<?php

use yii\db\Schema;
use yii\db\Migration;

class m151030_131732_create_table_acts_nmbers extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%acts_numbers}}',[
            'id' => Schema::TYPE_PK,
            'acts_number' => Schema::TYPE_INTEGER,
            'allow' => Schema::TYPE_BOOLEAN

        ],$tableOptions);

        $this->createIndex("idx_actnumb_acts_number", "{{%acts_numbers}}", "acts_number");
    }

    public function down()
    {
        $this->dropTable('{{%acts_numbers}}');
    }
}
