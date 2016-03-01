<?php

use yii\db\Migration;

class m160229_092721_add_column_to_enroll_request extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%enrollment_request}}','part_enroll',$this->boolean());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%enrollment_request}}','part_enroll');
    }
}
