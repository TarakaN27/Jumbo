<?php

use yii\db\Schema;
use yii\db\Migration;

class m151215_141910_add_colmn_to_table_crm_contact extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_cmp_contacts}}','created_by',$this->integer());
    }

    public function down()
    {
        $this->dropColumn('{{%crm_cmp_contacts}}','created_by');
    }
}
