<?php

use yii\db\Schema;
use yii\db\Migration;

class m150818_061316_add_colmn_to_units_for_manager extends Migration
{
    public function up()
    {
        $this->addColumn('{{%units_to_manager}}', 'pay_date', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%units_to_manager}}', 'pay_date');
    }

}
