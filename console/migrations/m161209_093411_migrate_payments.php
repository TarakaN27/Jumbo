<?php

use yii\db\Migration;

class m161209_093411_migrate_payments extends Migration
{
    public function up()
    {
        $this->createIndex('payment_order_idnx','{{%payment_request}}', 'payment_order');
    }

    public function down()
    {
        $this->dropIndex('payment_order_idnx','{{%payment_request}}');
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
