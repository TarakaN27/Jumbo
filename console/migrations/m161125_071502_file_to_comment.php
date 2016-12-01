<?php

use yii\db\Migration;

class m161125_071502_file_to_comment extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_cmp_file}}','message_id', $this->integer(11));
    }

    public function down()
    {
        $this->dropColumn('{{%crm_cmp_file}}','message_id');
        return true;
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
