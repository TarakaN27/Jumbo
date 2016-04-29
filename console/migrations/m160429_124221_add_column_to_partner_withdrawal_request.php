<?php

use yii\db\Migration;

class m160429_124221_add_column_to_partner_withdrawal_request extends Migration
{
    public function up()
    {
        $this->addColumn('{{%partner_withdrawal_request}}','description',$this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%partner_withdrawal_request}}','description');
    }
}
