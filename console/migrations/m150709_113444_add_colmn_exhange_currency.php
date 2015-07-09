<?php

use yii\db\Schema;
use yii\db\Migration;

class m150709_113444_add_colmn_exhange_currency extends Migration
{
    public function up()
    {
        $this->addColumn('{{%exchange_rates}}', 'is_default', Schema::TYPE_BOOLEAN);
        $this->addColumn('{{%exchange_rates}}', 'need_upd', Schema::TYPE_BOOLEAN);
    }

    public function down()
    {
        $this->dropColumn('{{%exchange_rates}}', 'is_default');
        $this->dropColumn('{{%exchange_rates}}', 'need_upd');
    }
}
