<?php

use yii\db\Schema;
use yii\db\Migration;

class m150810_081137_add_colmn_to_excahne_currency extends Migration
{
    public function up()
    {
        $this->addColumn('{{%exchange_rates}}', 'use_exchanger', Schema::TYPE_BOOLEAN);
        $this->addColumn('{{%exchange_rates}}', 'bank_id', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%exchange_rates}}', 'use_exchanger');
        $this->dropColumn('{{%exchange_rates}}', 'bank_id');
    }

}
