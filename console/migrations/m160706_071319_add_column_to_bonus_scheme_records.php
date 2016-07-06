<?php

use yii\db\Migration;

/**
 * Handles adding column to table `bonus_scheme_records`.
 */
class m160706_071319_add_column_to_bonus_scheme_records extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%bonus_scheme_records}}','deduct_lp',$this->text());
        $this->addColumn('{{%bonus_scheme_records_history}}','deduct_lp',$this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%bonus_scheme_records}}','deduct_lp');
        $this->dropColumn('{{%bonus_scheme_records_history}}','deduct_lp');
    }
}
