<?php

use yii\db\Schema;
use yii\db\Migration;

class m150708_130916_add_table_cuser_colmn extends Migration
{
    public function up()
    {
        $this->addColumn('{{%c_user}}', 'is_resident', Schema::TYPE_BOOLEAN);
        $this->addColumn('{{%c_user}}', 'r_country', Schema::TYPE_STRING);
        $this->addColumn('{{%c_user}}', 'requisites_id', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%c_user}}', 'is_resident');
        $this->dropColumn('{{%c_user}}', 'r_country');
        $this->dropColumn('{{%c_user}}', 'requisites_id');
    }
}
