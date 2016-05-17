<?php

use yii\db\Migration;

/**
 * Handles adding column to table `task_repeat`.
 */
class m160516_143201_add_column_to_task_repeat extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%crm_task_repeat}}','monthly_type',$this->smallInteger());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%crm_task_repeat}}','monthly_type');
    }
}
