<?php

use yii\db\Schema;
use yii\db\Migration;

class m151031_065114_add_colmn_to_acst extends Migration
{
    public function up()
    {
        $this->addColumn('{{%acts}}', 'lp_id', Schema::TYPE_INTEGER);
        $this->addColumn('{{%acts}}', 'ask', Schema::TYPE_STRING);
        $this->addColumn('{{%acts}}', 'file_name', Schema::TYPE_STRING);

        $this->addForeignKey("FK_acts_lp_id", "{{%acts}}", "lp_id", "{{%legal_person}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropColumn('{{%acts}}', 'lp_id');
        $this->dropColumn('{{%acts}}','ask');
        $this->dropColumn('{{%acts}}', 'file_name');
    }
}
