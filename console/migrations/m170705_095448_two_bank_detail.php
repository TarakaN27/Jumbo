<?php

use yii\db\Migration;

class m170705_095448_two_bank_detail extends Migration
{
    public function up()
    {

        $this->addColumn('{{%bank_details}}','bank_details_act', $this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%bank_details}}','bank_details_act');
    }
}
