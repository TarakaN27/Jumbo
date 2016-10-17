<?php

use yii\db\Migration;
use common\models\PaymentCondition;
class m161017_141525_hide_condition extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payment_condition}}','status', $this->integer(2));
        PaymentCondition::updateAll(['status'=>1]);
    }

    public function down()
    {
        $this->dropColumn('{{%payment_condition}}','status');
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
