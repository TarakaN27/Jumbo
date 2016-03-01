<?php

use yii\db\Migration;

class m160301_140102_remove_column_from_cuser extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%cuser_requisites}}','okpo');
        $this->dropColumn('{{%cuser_requisites}}','birthday');
        $this->addColumn('{{%cuser_requisites}}','bank_address',$this->string());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%cuser_requisites}}','bank_address');
        $this->addColumn('{{%cuser_requisites}}','okpo',$this->string());
        $this->addColumn('{{%cuser_requisites}}','birthday',$this->date());
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
