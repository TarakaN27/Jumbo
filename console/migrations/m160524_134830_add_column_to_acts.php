<?php

use yii\db\Migration;

/**
 * Handles adding column to table `acts`.
 */
class m160524_134830_add_column_to_acts extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%acts}}','currency_id',$this->integer());
        $this->addForeignKey('FK-act-currency_id','{{%acts}}','currency_id','{{%exchange_rates}}','id','SET NULL','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-act-currency_id','{{%acts}}');
        $this->dropColumn('{{%acts}}','currency_id');
    }
}
