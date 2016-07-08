<?php

use yii\db\Migration;

class m160708_095841_alterTableCuserSettings extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%cuser_settings}}','pp_max',$this->money(17,4));
    }

    public function down()
    {
        $this->alterColumn('{{%cuser_settings}}','pp_max',$this->integer());
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
