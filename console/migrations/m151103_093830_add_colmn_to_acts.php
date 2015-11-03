<?php

use yii\db\Schema;
use yii\db\Migration;

class m151103_093830_add_colmn_to_acts extends Migration
{
    public function up()
    {
        $this->addColumn('{{%acts}}', 'contract_num', Schema::TYPE_STRING);
        $this->addColumn('{{%acts}}', 'contract_date', Schema::TYPE_DATE);
    }

    public function down()
    {
        $this->dropColumn('{{%acts}}', 'contract_num');
        $this->dropColumn('{{%acts}}', 'contract_date');
    }

}
