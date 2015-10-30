<?php

use yii\db\Schema;
use yii\db\Migration;

class m151030_102137_add_colmn_to_acts extends Migration
{
    public function up()
    {
        $this->addColumn('{{%acts}}', 'act_num', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%acts}}', 'act_num');
    }

}
