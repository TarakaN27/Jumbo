<?php

use yii\db\Migration;

/**
 * Handles adding column to table `partner_scheme`.
 */
class m160614_125544_add_column_to_partner_scheme extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%partner_schemes}}','counting_base',$this->smallInteger()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%partner_schemes}}','counting_base');
    }
}
