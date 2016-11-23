<?php

use yii\db\Migration;

class m161123_081859_additional_currency_condition extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payment_condition}}','is_dub_currency', $this->integer(5));
        $this->addColumn('{{%payment_condition}}','dub_enroll_unit_id', $this->integer(5));
        $this->addColumn('{{%payment_condition}}','dub_cond_currency', $this->integer(5));
    }

    public function down()
    {
        $this->dropColumn('{{%payment_condition}}','is_dub_currency');
        $this->dropColumn('{{%payment_condition}}','dub_enroll_unit_id');
        $this->dropColumn('{{%payment_condition}}','dub_cond_currency');
        return true;
    }
}
