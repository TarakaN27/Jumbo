<?php

use yii\db\Schema;
use yii\db\Migration;

class m160120_121609_add_colmn_to_bill extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bills}}','offer_contract',$this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%bills}}','offer_contract');
    }

}
