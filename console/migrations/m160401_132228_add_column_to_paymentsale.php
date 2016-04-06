<?php

use yii\db\Migration;

class m160401_132228_add_column_to_paymentsale extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%payments_sale}}','payment_id',$this->integer());
        $this->addForeignKey('FK_pasl_payment_id','{{%payments_sale}}','payment_id','{{%payments}}','id','CASCADE','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_pasl_payment_id','{{%payments_sale}}');
        $this->dropColumn('{{%payments_sale}}','payment_id');
    }
}
