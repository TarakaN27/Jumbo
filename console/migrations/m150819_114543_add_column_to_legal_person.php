<?php

use yii\db\Schema;
use yii\db\Migration;

class m150819_114543_add_column_to_legal_person extends Migration
{
    public function up()
    {
        $this->addColumn('{{%legal_person}}', 'doc_requisites', Schema::TYPE_TEXT);
    }

    public function down()
    {
        $this->dropColumn('{{%legal_person}}', 'doc_requisites');
    }
}
