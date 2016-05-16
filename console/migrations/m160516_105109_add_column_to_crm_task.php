<?php

use yii\db\Migration;

/**
 * Handles adding column to table `crm_task`.
 */
class m160516_105109_add_column_to_crm_task extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%crm_task}}','repeat_task',$this->boolean()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%crm_task}}','repeat_task');
    }
}
