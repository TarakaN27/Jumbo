<?php

use yii\db\Migration;

class m160413_131534_add_column_to_cuser extends Migration
{
    /**
     *
     */
    public function safeUp()
    {
        $this->addColumn('{{%c_user}}','partner',$this->boolean()->defaultValue(0));
    }

    /**
     *
     */
    public function down()
    {
        $this->dropColumn('{{%c_user}}','partner');
    }
}
