<?php

use yii\db\Migration;

class m160922_144536_fixed_currency extends Migration
{
    public function up()
    {
        $this->addColumn('{{%exchange_rates}}','fix_exchange',$this->integer(2));
    }

    public function down()
    {
        $this->dropColumn('{{%exchange_rates}}','fix_exchange');
    }
}
