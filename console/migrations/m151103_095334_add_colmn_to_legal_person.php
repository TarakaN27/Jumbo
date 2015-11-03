<?php

use yii\db\Schema;
use yii\db\Migration;

class m151103_095334_add_colmn_to_legal_person extends Migration
{
    public function up()
    {
        $this->addColumn('{{%legal_person}}', 'act_tpl_id', Schema::TYPE_INTEGER);
        $this->addForeignKey("FK_lp_act_tpl_id", "{{%legal_person}}", "act_tpl_id", "{{%acts_template}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('act_tpl_id','{{%legal_person}}');
        $this->dropColumn('{{%legal_person}}', 'act_tpl_id');
    }
}
