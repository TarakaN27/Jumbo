<?php

use yii\db\Migration;

class m160408_124653_add_column_to_bonus_scheme extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bonus_scheme}}','payment_base',$this->smallInteger()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%bonus_scheme}}','payment_base');
    }
}
