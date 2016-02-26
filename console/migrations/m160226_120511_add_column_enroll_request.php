<?php

use yii\db\Migration;

class m160226_120511_add_column_enroll_request extends Migration
{
    public function up()
    {
        $this->addColumn('{{%enrollment_request}}','added_by',$this->integer());
        $this->addForeignKey('FK_enrlr_added_by','{{%enrollment_request}}','added_by','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_enrlr_added_by','{{%enrollment_request}}');
        $this->dropColumn('{{%enrollment_request}}','added_by');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
