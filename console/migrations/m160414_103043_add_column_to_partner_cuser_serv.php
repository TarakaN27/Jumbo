<?php

use yii\db\Migration;

class m160414_103043_add_column_to_partner_cuser_serv extends Migration
{
    public function up()
    {
        $this->addColumn("{{%partner_cuser_serv}}",'archive',$this->boolean()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn("{{%partner_cuser_serv}}",'archive');
    }
}
