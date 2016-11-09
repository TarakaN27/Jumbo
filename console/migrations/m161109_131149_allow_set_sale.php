<?php

use yii\db\Migration;

class m161109_131149_allow_set_sale extends Migration
{
    public function up()
    {
        $this->addColumn('{{%b_user}}','allow_set_sale', $this->integer(1));
    }

    public function down()
    {
        $this->dropColumn('{{%b_user}}','allow_set_sale');
        return true;
    }
}
