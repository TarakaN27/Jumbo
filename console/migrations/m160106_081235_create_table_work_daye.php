<?php

use yii\db\Schema;
use yii\db\Migration;

class m160106_081235_create_table_work_daye extends Migration
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
        $this->createTable('{{%work_day}}', [
            'id' =>$this->primaryKey(),
            'buser_id' => $this->integer()->notNull(),
            'log_date' => $this->date(),
            'spent_time' => $this->integer()->defaultValue(0),
            'begin_time' => $this->integer(),
            'end_time' => $this->integer(),
            'description' => $this->text(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->addForeignKey('FK_wd_buser_id','{{%work_day}}','buser_id','{{%b_user}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%work_day}}');
    }
}
