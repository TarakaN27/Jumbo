<?php

use yii\db\Schema;
use yii\db\Migration;

class m160215_142528_create_table_payments_quintity_hour extends Migration
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
        $this->createTable('{{%cuser_quantity_hour}}', [
            'id' =>$this->primaryKey(),
            'cuser_id' => $this->integer(),
            'hours' => $this->double(10),
            'spent_time' => $this->double(10),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey('FK_cqh_cuser_id','{{%cuser_quantity_hour}}','cuser_id','{{%c_user}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%cuser_quantity_hour}}');
    }

}
