<?php

use yii\db\Schema;
use yii\db\Migration;

class m151103_074554_create_table_services_default_contract extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%service_default_contract}}',[
            'id' => Schema::TYPE_PK,
            'service_id' => $this->integer()->notNull(),
            'lp_id' => $this->integer()->notNull(),
            'cont_number' => $this->string(),
            'cont_date' => $this->date(),
        ],$tableOptions);

        $this->addForeignKey("FK_sdc_serv_id", "{{%service_default_contract}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_sdc_lp_id", "{{%service_default_contract}}", "lp_id", "{{%legal_person}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%service_default_contract}}');
    }
}
