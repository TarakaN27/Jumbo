<?php

use yii\db\Schema;
use yii\db\Migration;

class m160106_131249_remove extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%b_user_crm_group}}','log_work_type');
        $this->addColumn('{{%b_user}}','log_work_type',$this->smallInteger());

    }

    public function down()
    {
        $this->addColumn('{{%b_user_crm_group}}','log_work_type',$this->smallInteger());
        $this->dropColumn('{{%b_user}}','log_work_type');
    }

}
