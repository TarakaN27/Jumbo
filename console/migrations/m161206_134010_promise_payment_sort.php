<?php

use yii\db\Migration;

class m161206_134010_promise_payment_sort extends Migration
{
    public function up()
    {
        $this->addColumn('{{%promised_payment}}','sort', $this->integer(3));
    }

    public function down()
    {
        $this->dropColumn('{{%promised_payment}}','sort');
        return false;
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
