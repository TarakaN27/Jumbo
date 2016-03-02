<?php

use yii\db\Migration;

class m160302_104554_add_column_to_legal_person extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%legal_person}}','admin_expense',$this->boolean());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%legal_person}}','admin_expense');
    }
}
