<?php

use yii\db\Migration;

class m160420_073532_add_column_to_partner_lead_link extends Migration
{
    public function up()
    {
        $this->addColumn("{{%partner_cuser_serv}}",'st_period_checked',$this->boolean()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn("{{%partner_cuser_serv}}",'st_period_checked');
    }
}
