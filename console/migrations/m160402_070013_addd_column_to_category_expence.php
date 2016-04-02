<?php

use yii\db\Migration;

class m160402_070013_addd_column_to_category_expence extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%expense_categories}}','private',$this->boolean());
    }

    public function safeDown()
    {
       $this->dropColumn('{{%expense_categories}}','private');
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
