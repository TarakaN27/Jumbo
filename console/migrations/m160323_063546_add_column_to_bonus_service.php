<?php

use yii\db\Migration;

class m160323_063546_add_column_to_bonus_service extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%bonus_scheme_service}}','legal_person',$this->text());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bonus_scheme_service}}','legal_person');
    }
}
