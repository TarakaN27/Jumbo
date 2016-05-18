<?php

use yii\db\Migration;

/**
 * Handles adding column to table `task`.
 */
class m160518_080142_add_column_to_task extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%crm_task}}','recurring_last_upd',$this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%crm_task}}','recurring_last_upd');
    }
}
