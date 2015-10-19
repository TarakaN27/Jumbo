<?php

use yii\db\Schema;
use yii\db\Migration;

class m151019_132613_add_colomn_to_settings extends Migration
{
    public function up()
    {
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Процент обещщеного платежа",
            "alias" => 'pp_percent',
            "value" => '10',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
    }

    public function down()
    {

    }

}
