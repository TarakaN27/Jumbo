<?php

use yii\db\Schema;
use yii\db\Migration;

class m150810_064217_add_colmn_to_currency extends Migration
{
    public function up()
    {
        $this->addColumn('{{%exchange_rates}}', 'use_base', Schema::TYPE_BOOLEAN);
        $this->addColumn('{{%exchange_rates}}', 'base_id', Schema::TYPE_INTEGER);
        $this->addColumn('{{%exchange_rates}}', 'factor', ' decimal (10,4) ');
    }

    public function down()
    {
        $this->dropColumn('{{%exchange_rates}}', 'use_base');
        $this->dropColumn('{{%exchange_rates}}', 'base_id');
        $this->dropColumn('{{%exchange_rates}}', 'factor');
    }
}
