<?php

use yii\db\Migration;

class m160428_131411_add_config_params extends Migration
{
    public function up()
    {
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Партнер. Бухгалтер для обработки.",
            "alias" => 'psw_bookkeeper_id',
            "value" => '',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
    }

    public function down()
    {
        echo "m160428_131411_add_config_params cannot be reverted.\n";

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
