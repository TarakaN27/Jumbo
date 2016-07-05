<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `boser_payment_records`.
 */
class m160705_073619_add_columns_to_boser_payment_records extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%b_user_payment_records}}','percents',$this->money(7,1));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%b_user_payment_records}}','percents');
    }
}
