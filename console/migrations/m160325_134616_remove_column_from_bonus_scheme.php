<?php

use yii\db\Migration;

class m160325_134616_remove_column_from_bonus_scheme extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%bonus_scheme}}','inactivity');
    }

    public function down()
    {
        $this->addColumn('{{%bonus_scheme}}','inactivity',$this->smallInteger());
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
