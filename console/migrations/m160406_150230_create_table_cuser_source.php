<?php

use yii\db\Migration;

class m160406_150230_create_table_cuser_source extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cuser_source}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->addColumn('{{%c_user}}','source_id',$this->integer());
        $this->createIndex('id-cussid','{{%c_user}}','source_id');
        $this->addForeignKey('FK_cur_source_id','{{%c_user}}','source_id','{{%cuser_source}}','id','SET NULL','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_cur_source_id','{{%c_user}}');
        $this->dropColumn('{{%c_user}}','source_id');
        $this->dropTable('{{%cuser_source}}');
    }
}
