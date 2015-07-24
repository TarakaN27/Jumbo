<?php

use yii\db\Schema;
use yii\db\Migration;

class m150724_122422_add_column_to_payment extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payments}}', 'prequest_id', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%payments}}', 'prequest_id');
    }

}
