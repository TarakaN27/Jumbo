<?php

use yii\db\Migration;

class m160325_125955_add_config extends Migration
{
    public function up()
    {
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Время бездействия клиента",
            "alias" => 'c_inactivity',
            "value" => '20',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
    }

    public function down()
    {
        $this->delete('{{%config}}',["alias" => 'c_inactivity']);
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
