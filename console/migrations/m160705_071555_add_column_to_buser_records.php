<?php

use yii\db\Migration;

/**
 * Handles adding column to table `buser_records`.
 */
class m160705_071555_add_column_to_buser_records extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%b_user_bonus}}','record_id',$this->integer());
        $this->createIndex('idx-bub-record_id','{{%b_user_bonus}}','record_id');
        $this->addForeignKey('FK-bub-record_id','{{%b_user_bonus}}','record_id','{{%b_user_payment_records}}','id','SET NULL','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-bub-record_id','{{%b_user_bonus}}');
        $this->dropColumn('{{%b_user_bonus}}','record_id');
    }
}
