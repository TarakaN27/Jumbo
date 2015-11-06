<?php

use yii\db\Schema;
use yii\db\Migration;

class m151105_063435_create_partner_purse extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        //добавляем таблицу для бекэнд пользователей
        $this->createTable('{{%partner_purse}}', [
            'id' => $this->primaryKey(),
            'partner_id' => $this->integer()->notNull(),
            'payments' => $this->money(),
            'acts' => $this->money(),
            'amount' => $this->money(),
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        //Добавляем связь
        $this->addForeignKey("FK_pp_partner_id", "{{%partner_purse}}", "partner_id", "{{%partner}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%partner_purse}}');
    }
}
