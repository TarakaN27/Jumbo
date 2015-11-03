<?php

use yii\db\Schema;
use yii\db\Migration;

class m151103_090310_crate_table_cuser_service_contract extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cuser_service_contract}}',[
            'id' => Schema::TYPE_PK,
            'service_id' => $this->integer()->notNull(),
            'cuser_id' => $this->integer()->notNull(),
            'cont_number' => $this->string(),
            'cont_date' => $this->date(),
        ],$tableOptions);

        $this->addForeignKey("FK_csc_serv_id", "{{%cuser_service_contract}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_csc_cuser_id", "{{%cuser_service_contract}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey("FK_sdc_serv_id",'{{%cuser_service_contract}}');
        $this->dropForeignKey("FK_sdc_cuser_id",'{{%cuser_service_contract}}');
        $this->dropTable('{{%cuser_service_contract}}');
    }


}
