<?php

use yii\db\Migration;

class m160222_070317_add_clumn_to_payment_request extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payment_condition}}','type',$this->smallInteger());
    }

    public function down()
    {
        $this->dropColumn('{{%payment_condition}}','type');
    }
}
