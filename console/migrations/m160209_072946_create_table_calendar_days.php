<?php

use yii\db\Schema;
use yii\db\Migration;

class m160209_072946_create_table_calendar_days extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        /**
         * Таблица учета работчего времени
         */
        $this->createTable('{{%calendar_days}}', [
            'id' =>$this->primaryKey(),
            'buser_id' => $this->integer(),
            'date' => $this->date(),
            'type' => $this->smallInteger(),
            'work_hour' => $this->smallInteger(),
            'description' => $this->text(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->addForeignKey('FK_cd_buser_id','{{%calendar_days}}','buser_id','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%calendar_days}}');
    }
}
