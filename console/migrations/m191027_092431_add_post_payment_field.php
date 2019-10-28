<?php

use yii\db\Migration;

class m191027_092431_add_post_payment_field extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payments}}', 'post_payment',$this->boolean()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%payments}}', 'post_payment');

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
