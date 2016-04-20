<?php

use yii\db\Migration;

class m160419_103049_add_column_to_partner_schemes extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%partner_schemes}}','currency_id',$this->integer());
        $this->addForeignKey('FK_par_sch_curr_id','{{%partner_schemes}}','currency_id','{{%exchange_rates}}','id','SET NULL','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_par_sch_curr_id','{{%partner_schemes}}');
        $this->dropColumn('{{%partner_schemes}}','currency_id');
    }
}
