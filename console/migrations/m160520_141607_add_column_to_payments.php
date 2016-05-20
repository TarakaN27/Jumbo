<?php

use yii\db\Migration;

/**
 * Handles adding column to table `payments`.
 */
class m160520_141607_add_column_to_payments extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%payments}}','act_close',$this->boolean()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%payments}}','act_close');
    }
}
