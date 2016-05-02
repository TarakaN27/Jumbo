<?php

use yii\db\Migration;

class m160502_070541_add_column_to_expense extends Migration
{
    /**
     *
     */
    public function safeUp()
    {
        $this->addColumn('{{%expense}}','pw_request_id',$this->integer());
        $this->addForeignKey('FK-exp_pwrid','{{%expense}}','pw_request_id','{{%partner_withdrawal_request}}','id','CASCADE','RESTRICT');
    }

    /**
     * 
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK-exp_pwrid','{{%expense}}');
        $this->dropColumn('{{%expense}}','pw_request_id');
    }
}
