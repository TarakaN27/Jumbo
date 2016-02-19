<?php

use yii\db\Migration;

class m160218_132130_create_table_prospects extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cuser_prospects}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);


        $this->addColumn('{{%c_user}}','prospects_id',$this->integer());
        $this->addForeignKey('FK_cur_prospects_id','{{%c_user}}','prospects_id','{{%cuser_prospects}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_cu_prospects_id','{{%c_user}}');
        $this->dropColumn('{{%c_user}}','prospects_id');
        $this->dropTable('{{%cuser_prospects}}');
    }
}
