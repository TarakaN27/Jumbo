<?php

use yii\db\Schema;
use yii\db\Migration;

class m151211_100347_add_colomn_to_cuser_requisites extends Migration
{
    public function up()
    {
        $this->addColumn('{{%cuser_requisites}}','site',$this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%cuser_requisites}}','site');
    }

}
