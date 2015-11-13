<?php

use yii\db\Schema;
use yii\db\Migration;

class m151110_071148_create_table_withdrawal extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_withdrawal}}', [
            'id' =>$this->primaryKey(),
            'partner_id' => $this->integer()->notNull(),
            'amount' => $this->money()->notNull(),
            'type' => $this->smallInteger(),
            'description' => $this->text(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%partner_withdrawal}}');
    }

}
