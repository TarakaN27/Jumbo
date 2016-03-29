<?php

use yii\db\Migration;

class m160328_070901_add_column_to_payment_sale extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payments_sale}}','sale_num',$this->smallInteger());
    }

    public function down()
    {
        $this->dropColumn('{{%payments_sale}}','sale_num');
    }
}
