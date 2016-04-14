<?php

use yii\db\Migration;

class m160413_133121_add_column_to_cuser extends Migration
{
    public function up()
    {
        $this->addColumn('{{%c_user}}','partner_manager_id',$this->integer());

        $this->createIndex('idx-cuserpmid','{{%c_user}}','partner_manager_id');
        $this->addForeignKey('FK_cuser_pmid','{{%c_user}}','partner_manager_id','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropIndex('idx-cuspmid','{{%c_user}}');
        $this->dropForeignKey('FK_cuser_pmid','{{%c_user}}');
        $this->dropColumn('{{%c_user}}','partner_manager_id');
    }
}
