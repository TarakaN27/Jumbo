<?php

use yii\db\Schema;
use yii\db\Migration;

class m151019_084612_add_colomn_to_bills extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bills}}', 'bsk', Schema::TYPE_STRING);
        $this->addColumn('{{%bills}}', 'external', Schema::TYPE_INTEGER);
        $this->createIndex('idx_bls_bsk','{{%bills}}','bsk');
    }

    public function down()
    {
        $this->dropIndex('idx_bls_bsk','{{%bills}}');
        $this->dropColumn('{{%bills}}', 'bsk');
        $this->dropColumn('{{%bills}}', 'external');
    }
}
