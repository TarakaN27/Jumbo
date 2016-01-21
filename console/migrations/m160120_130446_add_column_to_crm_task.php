<?php

use yii\db\Schema;
use yii\db\Migration;

class m160120_130446_add_column_to_crm_task extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_task}}','payment_request',$this->integer());
        $this->addForeignKey('FK_crmt_pay_req','{{%crm_task}}','payment_request','{{%payment_request}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropColumn('{{%crm_task}}','payment_request');
    }

}
