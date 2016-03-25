<?php

use yii\db\Migration;

class m160325_134148_remove_columns_from_service extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%services}}','c_inactivity');
    }

    public function down()
    {
        $this->addColumn('{{%services}}','c_inactivity',$this->smallInteger());
    }
}
