<?php

use yii\db\Migration;

class m160323_143116_add_column_to_buser_bonus extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%b_user_bonus}}','service_id',$this->integer());
        $this->addColumn('{{%b_user_bonus}}','cuser_id',$this->integer());

        $this->createIndex('bub_service_id','{{%b_user_bonus}}','service_id');
        $this->createIndex('bub_cuser_id','{{%b_user_bonus}}','cuser_id');

        $this->addForeignKey('FK_bub_service_id','{{%b_user_bonus}}','service_id','{{%services}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_bub_cuser_id','{{%b_user_bonus}}','cuser_id','{{%c_user}}','id','SET NULL','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%b_user_bonus}}','service_id');
        $this->dropColumn('{{%b_user_bonus}}','cuser_id');
    }
}
