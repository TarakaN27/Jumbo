<?php

use yii\db\Schema;
use yii\db\Migration;

class m151230_111505_add extends Migration
{
    public function up()
    {
        $this->addColumn('{{%c_user}}','archive',$this->boolean());
    }

    public function down()
    {
        $this->dropForeignKey('{{%c_user}}','archive');
    }

}
