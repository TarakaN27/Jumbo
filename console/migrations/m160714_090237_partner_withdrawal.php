<?php

use yii\db\Migration;

class m160714_090237_partner_withdrawal extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%partner_withdrawal_request}}','pending_in_base_currency',$this->money(17,4));
        $this->addColumn('{{%partner_w_bookkeeper_request}}','factual_amount_in_base_currency',$this->money(17,4));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%partner_withdrawal_request}}','pending_in_base_currency');
        $this->dropColumn('{{%partner_w_bookkeeper_request}}','factual_amount_in_base_currency');
    }
}
