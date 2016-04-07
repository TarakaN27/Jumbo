<?php

use yii\db\Migration;

class m160324_074258_add_column_to_service extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%services}}','c_inactivity',$this->smallInteger());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%services}}','c_inactivity');
    }
}
