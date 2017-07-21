<?php

use yii\db\Migration;

class m170721_073749_add_column_to_cuser_requisites extends Migration
{
    public function up()
    {
        $this->addColumn('{{%cuser_requisites}}', 'ext_email',$this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%cuser_requisites}}', 'ext_email');
    }
}
