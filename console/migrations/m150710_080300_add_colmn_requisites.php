<?php

use yii\db\Schema;
use yii\db\Migration;

class m150710_080300_add_colmn_requisites extends Migration
{
    public function up()
    {
        $this->addColumn('{{%cuser_requisites}}', 'type_id', Schema::TYPE_SMALLINT . ' NOT NULL');
        $this->addColumn('{{%cuser_requisites}}', 'birthday', Schema::TYPE_DATE);
        $this->addColumn('{{%cuser_requisites}}', 'pasp_series', Schema::TYPE_STRING);
        $this->addColumn('{{%cuser_requisites}}', 'pasp_number', Schema::TYPE_INTEGER);
        $this->addColumn('{{%cuser_requisites}}', 'pasp_ident', Schema::TYPE_STRING);
        $this->addColumn('{{%cuser_requisites}}', 'pasp_auth', Schema::TYPE_STRING);
        $this->addColumn('{{%cuser_requisites}}', 'pasp_date', Schema::TYPE_DATE);
    }

    public function down()
    {
        $this->dropColumn('{{%cuser_requisites}}', 'type_id');
        $this->dropColumn('{{%cuser_requisites}}', 'birthday');
        $this->dropColumn('{{%cuser_requisites}}', 'pasp_series');
        $this->dropColumn('{{%cuser_requisites}}', 'pasp_number');
        $this->dropColumn('{{%cuser_requisites}}', 'pasp_ident');
        $this->dropColumn('{{%cuser_requisites}}', 'pasp_auth');
        $this->dropColumn('{{%cuser_requisites}}', 'pasp_date');
    }
}
