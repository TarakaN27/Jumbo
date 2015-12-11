<?php

use yii\db\Schema;
use yii\db\Migration;

class m151211_071610_add_colmn_cuser extends Migration
{
    public function up()
    {
        $this->addColumn('{{%c_user}}','is_opened',$this->boolean());
        $this->addColumn('{{%c_user}}','created_by',$this->integer());
        $this->addForeignKey('FK_cu_created_by','{{%c_user}}','created_by','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_cu_created_by','{{%c_user}}');
        $this->dropColumn('{{%c_user}}','created_by');
        $this->dropColumn('{{%c_user}}','is_opened');
    }

}
