<?php

use yii\db\Migration;

/**
 * Handles adding column to table `partner_schemes`.
 */
class m160614_120424_add_column_to_partner_schemes extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%partner_schemes}}','turnover_type',$this->smallInteger()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%partner_schemes}}','turnover_intereval');
    }
}
