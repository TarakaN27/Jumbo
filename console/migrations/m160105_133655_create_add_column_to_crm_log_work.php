<?php

use yii\db\Schema;
use yii\db\Migration;

class m160105_133655_create_add_column_to_crm_log_work extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_task_log_time}}','log_date',$this->date());
    }

    public function down()
    {
        $this->dropColumn('{{%crm_task_log_time}}','log_date');
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
