<?php

use yii\db\Schema;
use yii\db\Migration;

class m150821_100344_add_column_to_legal_person extends Migration
{
    public function up()
    {
        $this->addColumn('{{%legal_person}}', 'doc_site', Schema::TYPE_TEXT);
        $this->addColumn('{{%legal_person}}', 'doc_email', Schema::TYPE_TEXT);

    }

    public function down()
    {
        $this->dropColumn('{{%legal_person}}', 'doc_site');
        $this->dropColumn('{{%legal_person}}', 'doc_email');
    }

}
