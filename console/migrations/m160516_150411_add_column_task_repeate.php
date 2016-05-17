<?php

use yii\db\Migration;

class m160516_150411_add_column_task_repeate extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%crm_task_repeat}}','monthly_days',$this->smallInteger());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%crm_task_repeat}}','monthly_days');
    }
}
