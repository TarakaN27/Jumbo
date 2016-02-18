<?php

use yii\db\Schema;
use yii\db\Migration;

class m160215_140640_create_table_service_rate_hist extends Migration
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
        $this->createTable('{{%service_rate_hist}}', [
            'id' =>$this->primaryKey(),
            'service_id' => $this->integer(),
            'date' => $this->date(),
            'old_rate' => $this->decimal(11,2),
            'new_rate' => $this->decimal(11,2),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->addForeignKey('FK_srh_service_id','{{%service_rate_hist}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%service_rate_hist}}');
    }
}
