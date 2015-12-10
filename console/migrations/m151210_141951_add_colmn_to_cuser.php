<?php

use yii\db\Schema;
use yii\db\Migration;

class m151210_141951_add_colmn_to_cuser extends Migration
{
    public function up()
    {
        $this->addColumn('{{%b_user}}', 'crm_group_id',$this->integer());
        $this->addForeignKey('FK_cu_crm_group_id','{{%b_user}}','crm_group_id','{{%b_user_crm_group}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropColumn('{{%b_user}}', 'crm_group_id');
    }
}
