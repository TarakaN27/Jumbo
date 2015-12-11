<?php

use yii\db\Schema;
use yii\db\Migration;

class m151211_074124_add_column_to_dialogs extends Migration
{
    public function up()
    {
        $this->addColumn('{{%dialogs}}','crm_cmp_id',$this->integer());
        $this->addForeignKey('FK_dia_crm_cmp_id','{{%dialogs}}','crm_cmp_id','{{%c_user}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_dia_crm_cmp_id','{{%dialogs}}');
        $this->dropColumn('{{%dialogs}}','crm_cmp_id');
    }

}
