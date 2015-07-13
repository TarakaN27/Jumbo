<?php

use yii\db\Schema;
use yii\db\Migration;

class m150713_094407_add_colmn_to_buser extends Migration
{
    public function up()
    {
        $this->addColumn('{{%b_user}}', 'fname', Schema::TYPE_STRING);
        $this->addColumn('{{%b_user}}', 'lname', Schema::TYPE_STRING);
        $this->addColumn('{{%b_user}}', 'mname', Schema::TYPE_STRING);
    }

    public function down()
    {
        $this->dropColumn('{{%b_user}}', 'fname');
        $this->dropColumn('{{%b_user}}', 'lname');
        $this->dropColumn('{{%b_user}}', 'mname');
    }
}
