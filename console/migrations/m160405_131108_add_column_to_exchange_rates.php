<?php

use yii\db\Migration;

class m160405_131108_add_column_to_exchange_rates extends Migration
{
    /**
     *
     */
    public function safeUp()
    {
        $this->addColumn('{{%exchange_rates}}', 'show_at_widget',$this->boolean()->defaultValue(0));
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropColumn('{{%exchange_rates}}', 'show_at_widget');
    }
}
