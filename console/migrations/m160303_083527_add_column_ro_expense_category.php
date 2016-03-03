<?php

use yii\db\Migration;

class m160303_083527_add_column_ro_expense_category extends Migration
{
    public function up()
    {
        $this->addColumn("{{%expense_categories}}",'without_cuser',$this->boolean());
    }

    public function down()
    {
        $this->dropColumn("{{%expense_categories}}",'without_cuser');
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
