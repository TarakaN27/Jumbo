<?php

use yii\db\Migration;

class m160325_144310_add_column_to_bonus_service_scheme extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bonus_scheme_service_history}}','simple_percent',$this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%bonus_scheme_service_history}}','simple_percent');
    }
}
