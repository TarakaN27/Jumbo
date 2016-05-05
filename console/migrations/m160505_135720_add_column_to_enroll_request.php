<?php

use yii\db\Migration;

class m160505_135720_add_column_to_enroll_request extends Migration
{
    /**
     * 
     */
    public function safeUp()
    {
        $this->addColumn('{{%enrollment_request}}','pw_request_id',$this->integer());
        $this->addForeignKey('FK-enrl-request','{{%enrollment_request}}','pw_request_id','{{%partner_withdrawal_request}}','id','CASCADE','RESTRICT');
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK-enrl-request','{{%enrollment_request}}');
        $this->dropColumn('{{%enrollment_request}}','pw_request_id');
    }
}
