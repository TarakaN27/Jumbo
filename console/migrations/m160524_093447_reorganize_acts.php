<?php

use yii\db\Migration;

class m160524_093447_reorganize_acts extends Migration
{
    public function up()
    {
        $this->dropForeignKey("FK_acts_service_id", "{{%acts}}");
        $this->dropForeignKey("FK_acts_template_id", "{{%acts}}");
        $this->dropColumn("{{%acts}}",'service_id');
        $this->dropColumn("{{%acts}}",'template_id');
        $this->dropColumn("{{%acts}}",'change');
    }

    public function down()
    {
        echo "m160524_093447_reorganize_acts cannot be reverted.\n";
        return false;
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
