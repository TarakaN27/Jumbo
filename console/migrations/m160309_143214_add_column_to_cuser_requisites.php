<?php

use yii\db\Migration;

class m160309_143214_add_column_to_cuser_requisites extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%c_user}}','allow_expense',$this->boolean()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%c_user}}','allow_expense');
    }
}
