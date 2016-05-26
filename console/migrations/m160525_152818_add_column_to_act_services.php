<?php

use yii\db\Migration;

/**
 * Handles adding column to table `act_services`.
 */
class m160525_152818_add_column_to_act_services extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%act_services}}','job_description',$this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%act_services}}','job_description');
    }
}
