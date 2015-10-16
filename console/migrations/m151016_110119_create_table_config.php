<?php

use yii\db\Schema;
use yii\db\Migration;

class m151016_110119_create_table_config extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%config}}',[
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING.' NOT NULL',
            'alias' => Schema::TYPE_STRING.' NOT NULL',
            'value' => Schema::TYPE_STRING.' NOT NULL ',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ],$tableOptions);

        $this->createIndex("idx_conf_alias", "{{%config}}", "alias");

        $this->insert('{{%config}}', array(
            "id" => "",
            "name" => "Мин. сумма платежа по счету",
            "alias" => 'min_bill_amount',
            "value" => '2500000',
            "created_at"=>time(),
            "updated_at"=>time()
        ));

    }

    public function down()
    {
        $this->dropTable('{{%config}}');
    }

}
