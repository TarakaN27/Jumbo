<?php

use yii\db\Schema;
use yii\db\Migration;

class m160216_095702_add_colomn_to_settings extends Migration
{
    public function up()
    {
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Ставка норма часа по услугам",
            "alias" => 'qh_rate',
            "value" => '0',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
    }

    public function down()
    {
        echo "m160216_095702_add_colomn_to_settings cannot be reverted.\n";

        return false;
    }

}
