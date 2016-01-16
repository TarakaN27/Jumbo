<?php

use yii\db\Schema;
use yii\db\Migration;

class m160116_133300_add_column_to_dialogs extends Migration
{
    public function up()
    {
        $this->addColumn('{{%dialogs}}','crm_task_id',$this->integer());
        $this->addForeignKey('FK_dia_crm_task_id','{{%dialogs}}','crm_task_id','{{%crm_task}}','id','CASCADE','RESTRICT');
        $this->dropForeignKey('FK_crmt_dialog','{{%crm_task}}');
        $this->dropColumn('{{%crm_task}}','dialog_id');

    }

    public function down()
    {
        $this->addColumn('{{%crm_task}}','dialog_id', $this->integer());
        $this->addForeignKey('FK_crmt_dialog','{{%crm_task}}','dialog_id','{{%dialogs}}','id','SET NULL','RESTRICT');
        $this->dropForeignKey('FK_dia_crm_task_id','{{%dialogs}}');
        $this->dropColumn('{{%dialogs}}','crm_task_id');;
    }


}
