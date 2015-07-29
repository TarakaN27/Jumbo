<?php

use yii\db\Schema;
use yii\db\Migration;

class m150729_073521_create_table_bind_user extends Migration
{
    public function up()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bind_buser}}', [
            'buser_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'member_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (buser_id,member_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_buser_id", "{{%bind_buser}}", "buser_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_member_id", "{{%bind_buser}}", "member_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%bind_buser}}');
    }
}
