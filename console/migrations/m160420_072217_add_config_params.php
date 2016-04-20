<?php

use yii\db\Migration;

class m160420_072217_add_config_params extends Migration
{
    public function up()
    {
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Партнерские схемы. Стартовый период",
            "alias" => 'ps_start_period',
            "value" => '3',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Партнерские схемы. Регулярный период",
            "alias" => 'ps_regular_period',
            "value" => '4',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
    }

    public function down()
    {
        echo "m160420_072217_add_config_params cannot be reverted.\n";

        return false;
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
