<?php

use yii\db\Schema;
use yii\db\Migration;

class m151216_105710_add_colmn_to_crm_cmp_file extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_cmp_file}}','contact_id',$this->integer());
        $this->addForeignKey('FK_ccf_contact_id','{{%crm_cmp_file}}','contact_id','{{%crm_cmp_contacts}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_ccf_contact_id','{{%crm_cmp_file}}');
        $this->dropColumn('{{%crm_cmp_file}}','contact_id');
    }

}
