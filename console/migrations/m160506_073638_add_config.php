<?php

use yii\db\Migration;

class m160506_073638_add_config extends Migration
{
    public function up()
    {
        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Партнер. Партнерского договор.",
            "alias" => 'partnership_agreement',
            "value" => '',
            "created_at"=>time(),
            "updated_at"=>time()
        ));
    }

    public function down()
    {
        echo "m160506_073638_add_config cannot be reverted.\n";

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
