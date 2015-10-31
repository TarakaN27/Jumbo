<?php

use yii\db\Schema;
use yii\db\Migration;

class m151031_080124_add_colmn_to_acts extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%acts}}', 'use_vat', Schema::TYPE_BOOLEAN);
        $this->addColumn('{{%acts}}', 'vat_rate', Schema::TYPE_DOUBLE);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%acts}}', 'use_vat');
        $this->dropColumn('{{%acts}}', 'vat_rate');
    }

}
