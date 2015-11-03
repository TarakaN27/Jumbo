<?php

use yii\db\Schema;
use yii\db\Migration;

class m151103_093559_drop_colmn_from_acts extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%acts}}', 'use_vat');
        $this->dropColumn('{{%acts}}', 'vat_rate');
    }

    public function down()
    {
        $this->addColumn('{{%acts}}', 'use_vat', Schema::TYPE_BOOLEAN);
        $this->addColumn('{{%acts}}', 'vat_rate', Schema::TYPE_DOUBLE);
    }

}
