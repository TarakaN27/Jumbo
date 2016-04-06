<?php

use yii\db\Migration;

class m160323_100830_add_column_to_bonus_history extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bonus_scheme_service_history}}','legal_person',$this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%bonus_scheme_service_history}}','legal_person');
    }
}
