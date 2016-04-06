<?php

use yii\db\Migration;

class m160406_134024_add_column_to_cuser extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%c_user}}','manager_crc_id',$this->integer());
        $this->createIndex('idx-cu-mancrcid','{{%c_user}}','manager_crc_id');
        $this->addForeignKey('FK_cu_man_crc_id','{{%c_user}}','manager_crc_id','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_cu_man_crc_id','{{%c_user}}');
        $this->dropColumn('{{%c_user}}','manager_crc_id');
    }
}
