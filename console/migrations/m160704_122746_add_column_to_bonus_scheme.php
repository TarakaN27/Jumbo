<?php

use yii\db\Migration;

/**
 * Handles adding column to table `bonus_scheme`.
 */
class m160704_122746_add_column_to_bonus_scheme extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%bonus_scheme}}','currency_id',$this->integer());
        $this->createIndex('idx-bs-currency_id','{{%bonus_scheme}}','currency_id');
        $this->addForeignKey('FK-bs-currency_id','{{%bonus_scheme}}','currency_id',"{{%exchange_rates}}", "id",'SET NULL','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-bs-currency_id','{{%bonus_scheme}}');
        $this->dropColumn('{{%bonus_scheme}}','currency_id');
    }
}
