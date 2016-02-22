<?php

use yii\db\Migration;

class m160222_131146_ad_column_to_exchange_curr extends Migration
{
    public function up()
    {
        $this->addColumn('{{%exchange_rates}}', 'use_rur_for_byr', $this->boolean());
    }

    public function down()
    {
        $this->dropColumn('{{%exchange_rates}}', 'use_rur_for_byr');
    }

}
