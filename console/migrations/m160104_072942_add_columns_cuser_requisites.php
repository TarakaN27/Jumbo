<?php

use yii\db\Schema;
use yii\db\Migration;

class m160104_072942_add_columns_cuser_requisites extends Migration
{
    public function up()
    {
        $this->addColumn('{{%cuser_requisites}}','description',$this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%cuser_requisites}}','description');
    }

}
