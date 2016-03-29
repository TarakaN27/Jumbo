<?php

use yii\db\Migration;

class m160329_120013_drop_tables_units extends Migration
{
    public function up()
    {
        $this->dropForeignKey("FK_unit_service_id", "{{%units}}");
        $this->dropForeignKey("FK_unit_cuser_id", "{{%units}}");
        $this->dropForeignKey("FK_unit_cs_h_unit_id", "{{%units_cost_history}}");
        $this->dropForeignKey("FK_unit_man_unit_id", "{{%units_to_manager}}");
        $this->dropForeignKey("FK_unit_man_manager_id", "{{%units_to_manager}}");
        $this->dropForeignKey("FK_unit_man_payment_idd", "{{%units_to_manager}}");
        $this->dropTable('{{%units}}');
        $this->dropTable('{{%units_cost_history}}');
        $this->dropTable('{{%units_to_manager}}');
    }

    public function down()
    {

    }
}
