<?php

use yii\db\Schema;
use yii\db\Migration;

class m151215_140103_add_colmn_to_table_crm_contact extends Migration
{
    public function up()
    {
        $this->addColumn('{{%crm_cmp_contacts}}','is_opened',$this->boolean());
    }

    public function down()
    {
        $this->dropColumn('{{%crm_cmp_contacts}}','is_opened');
    }
}
