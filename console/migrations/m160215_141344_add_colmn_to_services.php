<?php

use yii\db\Schema;
use yii\db\Migration;

class m160215_141344_add_colmn_to_services extends Migration
{
    public function up()
    {
        $this->addColumn('{{%services}}','rate',$this->decimal(11,2));
    }

    public function down()
    {
        $this->dropColumn('{{%services}}','rate');
    }

}
