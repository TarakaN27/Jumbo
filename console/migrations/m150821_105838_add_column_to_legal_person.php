<?php

use yii\db\Schema;
use yii\db\Migration;

class m150821_105838_add_column_to_legal_person extends Migration
{
    public function up()
    {
        $this->addColumn('{{%legal_person}}', 'use_vat', Schema::TYPE_BOOLEAN. ' DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%legal_person}}', 'use_vat');
    }

}
