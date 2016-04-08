<?php

use yii\db\Migration;

class m160407_110903_add_column_to_messages extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%messages}}','technical',$this->boolean()->defaultValue(0));
    }
    
    public function safeDown()
    {
        $this->dropColumn('{{%messages}}','technical');
    }
}
