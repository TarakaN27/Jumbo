<?php

use yii\db\Schema;
use yii\db\Migration;

class m160120_075444_add_payment_order extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payment_request}}','payment_order',$this->string());
        $this->addColumn('{{%payments}}','payment_order',$this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%payment_request}}','payment_order');
        $this->dropColumn('{{%payments}}','payment_order');
    }

}
