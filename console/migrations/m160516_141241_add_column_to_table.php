<?php

use yii\db\Migration;

/**
 * Handles adding column to table `table`.
 */
class m160516_141241_add_column_to_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%crm_task_repeat}}','week',$this->smallInteger());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%crm_task_repeat}}','week');
    }
}
