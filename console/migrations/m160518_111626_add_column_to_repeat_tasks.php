<?php

use yii\db\Migration;

/**
 * Handles adding column to table `repeat_tasks`.
 */
class m160518_111626_add_column_to_repeat_tasks extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%crm_task_repeat}}','counter_repeat',$this->integer()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%crm_task_repeat}}','counter_repeat');
    }
}
