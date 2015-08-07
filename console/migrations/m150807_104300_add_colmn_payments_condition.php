<?php

use yii\db\Schema;
use yii\db\Migration;

class m150807_104300_add_colmn_payments_condition extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payment_condition}}', 'cond_currency', Schema::TYPE_INTEGER);

    }

    public function down()
    {
        $this->dropColumn('{{%payment_condition}}', 'cond_currency');
    }
}
