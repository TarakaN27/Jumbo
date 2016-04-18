<?php

use yii\db\Migration;

class m160418_094844_create_table_partner_allow_service extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_allow_service}}', [
            'cuser_id' => $this->integer()->notNull(),
            'service_id' => $this->integer()->notNull(),
            'PRIMARY KEY (cuser_id,service_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_pals_cuser_id", "{{%partner_allow_service}}", 'cuser_id', "{{%c_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_pals_scheme_id", "{{%partner_allow_service}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%partner_allow_service}}');
    }
}
