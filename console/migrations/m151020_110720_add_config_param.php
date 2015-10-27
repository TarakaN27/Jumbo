<?php

use yii\db\Schema;
use yii\db\Migration;

class m151020_110720_add_config_param extends Migration
{
    public function up()
    {
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Максимальная сумма обещанного платежа",
            "alias" => 'pp_max',
            "value" => '1000000',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
    }

    public function down()
    {

    }

}
