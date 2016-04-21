<?php

use yii\db\Migration;

class m160421_114809_add_column_to_partner_purse_hisotry extends Migration
{
    public function up()
    {
        $this->addColumn('{{%partner_purse_history}}','percent',$this->float(7));
    }

    public function down()
    {
        $this->dropColumn('{{%partner_purse_history}}','percent');
    }
}
