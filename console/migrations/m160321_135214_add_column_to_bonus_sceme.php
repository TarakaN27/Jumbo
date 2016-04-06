<?php

use yii\db\Migration;

class m160321_135214_add_column_to_bonus_sceme extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bonus_scheme}}','infinite',$this->boolean());
    }

    public function down()
    {
        $this->dropColumn('{{%bonus_scheme}}','infinite');
    }
}
