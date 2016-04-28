<?php

use yii\db\Migration;

class m160427_143213_add_column_to_legal_person extends Migration
{
    /**
     * 
     */
    public function safeUp()
    {
        $this->addColumn('{{%legal_person}}','partner_cntr',$this->boolean()->defaultValue(0));
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropColumn('{{%legal_person}}','partner_cntr');

    }
}
