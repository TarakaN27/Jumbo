<?php

use yii\db\Migration;

/**
 * Handles adding column to table `legal_person`.
 */
class m160530_111517_add_column_to_legal_person extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%legal_person}}','ynp',$this->string());
        $this->addColumn('{{%legal_person}}','mailing_address',$this->string());
        $this->addColumn('{{%legal_person}}','telephone_number',$this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%legal_person}}','ynp');
        $this->dropColumn('{{%legal_person}}','mailing_address');
        $this->dropColumn('{{%legal_person}}','telephone_number');
    }
}
