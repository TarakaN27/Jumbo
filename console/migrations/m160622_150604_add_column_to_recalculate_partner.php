<?php

use yii\db\Migration;

/**
 * Handles adding column to table `recalculate_partner`.
 */
class m160622_150604_add_column_to_recalculate_partner extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%recalculate_partner}}','service_id',$this->integer());
        $this->createIndex('idx-rec-par-serv_id','{{%recalculate_partner}}','service_id');
        $this->addForeignKey('FK-rec-par-serv_id','{{%recalculate_partner}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-rec-par-serv_id','{{%recalculate_partner}}');
        $this->dropColumn('{{%recalculate_partner}}','service_id');
    }
}
