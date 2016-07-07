<?php

use yii\db\Migration;

/**
 * Handles adding column to table `legal_person`.
 */
class m160707_094257_add_column_to_legal_person extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%legal_person}}','letter_tpl_type',$this->smallInteger()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%legal_person}}','letter_tpl_type');
    }
}
