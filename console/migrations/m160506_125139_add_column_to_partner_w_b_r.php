<?php

use yii\db\Migration;

class m160506_125139_add_column_to_partner_w_b_r extends Migration
{
    public function up()
    {
        $this->addColumn('{{%partner_w_bookkeeper_request}}','description',$this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%partner_w_bookkeeper_request}}','description');
    }
}
