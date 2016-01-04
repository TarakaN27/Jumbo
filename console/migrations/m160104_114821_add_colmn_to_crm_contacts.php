<?php

use yii\db\Schema;
use yii\db\Migration;

class m160104_114821_add_colmn_to_crm_contacts extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_cmp_contacts}}','ext_id',$this->string());
    }

    public function down()
    {
       $this->dropColumn('{{%crm_cmp_contacts}}','ext_id');
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
