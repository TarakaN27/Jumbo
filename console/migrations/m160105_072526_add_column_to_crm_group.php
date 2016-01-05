<?php

use yii\db\Schema;
use yii\db\Migration;

class m160105_072526_add_column_to_crm_group extends Migration
{
    public function up()
    {
        $this->addColumn('{{%b_user_crm_group}}','log_work_type',$this->smallInteger());
    }

    public function down()
    {
        $this->dropColumn('{{%b_user_crm_group}}','log_work_type');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
