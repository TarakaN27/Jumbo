<?php

use yii\db\Schema;
use yii\db\Migration;

class m151211_080806_add_colmn_to_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_cmp_contacts}}','phone',$this->string());
        $this->addColumn('{{%crm_cmp_contacts}}','email',$this->string());
        $this->addColumn('{{%dialogs}}','crm_cmp_contact_id',$this->integer());
        $this->addForeignKey('FK_dia_crm_cmp_contact_id','{{%dialogs}}','crm_cmp_contact_id','{{%crm_cmp_contacts}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_dia_crm_cmp_contact_id','{{%dialogs}}');
        $this->dropColumn('{{%dialogs}}','crm_cmp_contact_id');
        $this->dropColumn('{{%crm_cmp_contacts}}','email');
        $this->dropColumn('{{%crm_cmp_contacts}}','phone');
    }

}
