<?php

use yii\db\Schema;
use yii\db\Migration;

class m150914_070720_add_colmn_to_legal_person extends Migration
{
    public function up()
    {
        $this->addColumn('{{%legal_person}}', 'docx_id', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%legal_person}}', 'docx_id');
    }
}
