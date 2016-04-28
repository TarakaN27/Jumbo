<?php

use yii\db\Migration;

class m160428_114352_create_table_partner_bookkeeper_request extends Migration
{
    public function up()
    {
        $this->createTable('table_partner_bookkeeper_request', [
            'id' => $this->primaryKey()
        ]);
    }

    public function down()
    {
        $this->dropTable('table_partner_bookkeeper_request');
    }
}
