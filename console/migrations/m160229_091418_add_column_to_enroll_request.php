<?php

use yii\db\Migration;

class m160229_091418_add_column_to_enroll_request extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%enrollment_request}}','parent_id',$this->integer());
        $this->addForeignKey('FK_enrlr_parent_id','{{%enrollment_request}}','parent_id','{{%enrollment_request}}','id','CASCADE','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%enrollment_request}}','parent_id');
    }
}
