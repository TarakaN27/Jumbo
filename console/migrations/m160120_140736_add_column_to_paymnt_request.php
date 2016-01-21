<?php

use yii\db\Schema;
use yii\db\Migration;

class m160120_140736_add_column_to_paymnt_request extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payment_request}}','service_id',$this->string());
    }

    public function down()
    {
       $this->dropColumn('{{%payment_request}}','service_id');
    }

}
