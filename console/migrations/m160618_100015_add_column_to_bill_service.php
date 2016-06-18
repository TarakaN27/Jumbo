<?php

use yii\db\Migration;

/**
 * Handles adding column to table `bill_service`.
 */
class m160618_100015_add_column_to_bill_service extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%bill_services}}','ordering',$this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%bill_services}}','ordering');
    }
}
