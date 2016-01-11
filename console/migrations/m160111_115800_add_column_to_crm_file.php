<?php

use yii\db\Schema;
use yii\db\Migration;

class m160111_115800_add_column_to_crm_file extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_cmp_file}}','task_id',$this->integer());
        $this->addForeignKey('FK_crm_cmp_file','{{%crm_cmp_file}}','task_id','{{%crm_task}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropColumn('{{%crm_cmp_file}}','task_id');
    }
}
