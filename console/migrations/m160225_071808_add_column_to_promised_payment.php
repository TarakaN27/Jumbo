<?php

use yii\db\Migration;

class m160225_071808_add_column_to_promised_payment extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%promised_payment}}','description',$this->string());
        $this->addColumn('{{%promised_payment}}','owner',$this->integer());
        $this->addForeignKey('FK_prpay_owner','{{%promised_payment}}','owner','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_prpay_owner','{{%promised_payment}}');
        $this->dropColumn('{{%promised_payment}}','description');
        $this->dropColumn('{{%promised_payment}}','owner');
    }
}
