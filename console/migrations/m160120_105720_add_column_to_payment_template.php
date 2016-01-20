<?php

use yii\db\Schema;
use yii\db\Migration;

class m160120_105720_add_column_to_payment_template extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bill_template}}','offer_contract',$this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%bill_template}}','offer_contract');
    }
}
