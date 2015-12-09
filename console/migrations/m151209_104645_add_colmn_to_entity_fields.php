<?php

use yii\db\Schema;
use yii\db\Migration;

class m151209_104645_add_colmn_to_entity_fields extends Migration
{
    public function up()
    {
        $this->addColumn('{{%entity_fields}}', 'options', Schema::TYPE_TEXT);
    }

    public function down()
    {
        $this->dropColumn('{{%entity_fields}}', 'options');
    }


}
