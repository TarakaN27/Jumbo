<?php

use yii\db\Migration;

class m160316_133110_add_column_to_buser extends Migration
{
    public function up()
    {
        $this->addColumn('{{%b_user}}','allow_unit',$this->boolean());
    }

    public function down()
    {
        $this->dropColumn('{{%b_user}}','allow_unit');
    }
}
