<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `act_services`.
 */
class m160530_120019_add_columns_to_act_services extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%act_services}}','ordering',$this->smallInteger());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%act_services}}','ordering');
    }
}
