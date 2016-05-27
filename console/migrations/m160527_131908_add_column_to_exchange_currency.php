<?php

use yii\db\Migration;

/**
 * Handles adding column to table `exchange_currency`.
 */
class m160527_131908_add_column_to_exchange_currency extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%exchange_rates}}','doc_n2w_type',$this->smallInteger()->defaultValue('0'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%exchange_rates}}','doc_n2w_type');
    }
}
