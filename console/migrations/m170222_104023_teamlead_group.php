<?php

use yii\db\Migration;

class m170222_104023_teamlead_group extends Migration
{
    public function up()
    {
        $this->addColumn('{{%b_user}}','group_members', $this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%b_user}}','group_members');
        return true;
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
