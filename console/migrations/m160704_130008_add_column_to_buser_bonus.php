<?php

use yii\db\Migration;

/**
 * Handles adding column to table `buser_bonus`.
 */
class m160704_130008_add_column_to_buser_bonus extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%b_user_bonus}}','currency_id',$this->integer());
        $this->createIndex('idx-bub-currency_id','{{%b_user_bonus}}','currency_id');
        $this->addForeignKey('FK-bub-currency_id','{{%b_user_bonus}}','currency_id',"{{%exchange_rates}}", "id",'SET NULL','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-bub-currency_id','{{%b_user_bonus}}');
        $this->dropColumn('{{%b_user_bonus}}','currency_id');
    }
}
