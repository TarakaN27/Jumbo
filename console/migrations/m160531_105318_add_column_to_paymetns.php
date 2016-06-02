<?php

use yii\db\Migration;

/**
 * Handles adding column to table `paymetns`.
 */
class m160531_105318_add_column_to_paymetns extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%payments}}','hide_act_payment',$this->boolean()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%payments}}','hide_act_payment');
    }
}
