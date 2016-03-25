<?php

use yii\db\Migration;

class m160325_112501_add_column_to_expense_category extends Migration
{
    public function safeUp()
    {
        $this->addColumn("{{%expense_categories}}",'ignore_at_report',$this->boolean());
    }

    public function safedown()
    {
        $this->dropColumn("{{%expense_categories}}",'ignore_at_report');
    }
}
