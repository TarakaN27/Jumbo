<?php

use yii\db\Migration;

class m170606_123906_new_field_bik extends Migration
{
    public function up()
    {
        $this->addColumn('{{%cuser_requisites}}','bik', $this->string(8));
    }

    public function down()
    {
        $this->dropColumn('{{%cuser_requisites}}','bik');
    }
}
