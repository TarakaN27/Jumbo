<?php

use yii\db\Migration;

/**
 * Handles adding column to table `legal_person`.
 */
class m160526_140417_add_column_to_legal_person extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%legal_person}}','address',$this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%legal_person}}','address');
    }
}
