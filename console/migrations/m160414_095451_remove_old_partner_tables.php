<?php

use yii\db\Migration;

class m160414_095451_remove_old_partner_tables extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey("FK_pcs_partner_id","{{%partner_cuser_serv}}");
        $this->addForeignKey("FK_pcs_partner_id", "{{%partner_cuser_serv}}", "partner_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');

        $this->dropTable('{{%partner_purse}}');
        $this->dropTable('{{%partner_profit}}');
        $this->dropTable('{{%partner_withdrawal}}');
        $this->dropTable('{{%partner_condition}}');
        $this->dropTable('{{%partner}}');

    }

    public function down()
    {
        echo "m160414_095451_remove_old_partner_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
