<?php

use yii\db\Schema;
use yii\db\Migration;

class m151104_110259_create_table_partner_cuser_serv extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        //добавляем таблицу для бекэнд пользователей
        $this->createTable('{{%partner_cuser_serv}}', [
            'id' => $this->primaryKey(),
            'partner_id' => $this->integer()->notNull(),
            'cuser_id' => $this->integer()->notNull(),
            'service_id' => $this->integer()->notNull(),
            'connect' => $this->date(),
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("FK_pcs_partner_id", "{{%partner_cuser_serv}}", "partner_id", "{{%partner}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_pcs_cuser_id", "{{%partner_cuser_serv}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_pcs_service_id", "{{%partner_cuser_serv}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');

    }

    public function down()
    {
        $this->dropForeignKey("FK_pcs_cuser_id","{{%partner_cuser_serv}}");
        $this->dropForeignKey("FK_pcs_partner_id","{{%partner_cuser_serv}}");
        $this->dropForeignKey("FK_pcs_service_id","{{%partner_cuser_serv}}");
        $this->dropTable('{{%partner_cuser_serv}}');
    }

}
