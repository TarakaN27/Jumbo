<?php

use yii\db\Schema;
use yii\db\Migration;

class m151105_121718_create_table_partner_profit extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_profit}}', [
            'id' =>$this->primaryKey(),
            'partner_id' => $this->integer()->notNull(),
            'act_id' => $this->integer()->notNull(),
            'cond_id' => $this->integer()->notNull(),
            'amount' => $this->money()->notNull(),
            'percent' => $this->double()->notNull(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        //Добавляем связь
        $this->addForeignKey("FK_pprf_partner_id", "{{%partner_profit}}", "partner_id", "{{%partner}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_pprf_act_id", "{{%partner_profit}}", "act_id", "{{%acts}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_pprf_cond_id", "{{%partner_profit}}", "cond_id", "{{%partner_condition}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%partner_profit}}');
    }

}
