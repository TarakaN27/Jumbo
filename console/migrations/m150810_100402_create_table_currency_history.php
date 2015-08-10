<?php

use yii\db\Schema;
use yii\db\Migration;

class m150810_100402_create_table_currency_history extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%exchange_currency_history}}', [
            'id' => Schema::TYPE_PK,
            'currency_id' => Schema::TYPE_INTEGER,
            'rate_nbrb' => ' decimal (19,10) NOT NULL',
            'old_rate_nbrb' =>  ' decimal (19,10) NOT NULL',
            'rate_cbr' => ' decimal (19,10) NOT NULL',
            'old_rate_cbr' =>  ' decimal (19,10) NOT NULL',
            'user_id' => Schema::TYPE_INTEGER,
            'date' =>  Schema::TYPE_DATE,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("FK_ech_currency_id", "{{%exchange_currency_history}}", "currency_id", "{{%exchange_rates}}", "id", 'CASCADE','RESTRICT');

    }

    public function down()
    {
        $this->dropTable('{{%exchange_currency_history}}');
    }
    

}
