<?php

use yii\db\Migration;

class m170602_135509_new_ch_account extends Migration
{
    public function up()
    {
        $this->addColumn('{{%cuser_requisites}}','new_ch_account', $this->string(34));
    }

    public function down()
    {
        $this->dropColumn('{{%cuser_requisites}}','new_ch_account');
    }

}
