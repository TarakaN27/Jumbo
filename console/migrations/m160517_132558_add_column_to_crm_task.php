<?php

use yii\db\Migration;

/**
 * Handles adding column to table `crm_task`.
 */
class m160517_132558_add_column_to_crm_task extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%crm_task}}','recurring_id',$this->integer());
        $this->createIndex('idx-crmt-recid','{{%crm_task}}','recurring_id');
        $this->addForeignKey('FK-crmt-recurring_id','{{%crm_task}}','recurring_id','{{%crm_task}}','id','SET NULL','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('idx-crmt-recid','{{%crm_task}}');
        $this->dropForeignKey('FK-crmt-recurring_id','{{%crm_task}}');
        $this->dropColumn('{{%crm_task}}','recurring_id');
    }
}
