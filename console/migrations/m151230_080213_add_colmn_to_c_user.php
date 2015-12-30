<?php

use yii\db\Schema;
use yii\db\Migration;

class m151230_080213_add_colmn_to_c_user extends Migration
{
    public function up()
    {
        $this->addColumn('{{%c_user}}','contractor',$this->boolean());
    }

    public function down()
    {
        $this->dropForeignKey('{{%c_user}}','contractor');
    }

}
