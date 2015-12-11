<?php

use yii\db\Schema;
use yii\db\Migration;

class m151211_082307_create_table_crm_logs extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        /**
         * Таблица ролей для пользователй CRM
         */
        $this->createTable('{{%crm_logs}}', [
            'id' =>$this->primaryKey(),
            'entity' => $this->string()->notNull(),
            'item_id' => $this->integer(),
            'changed_by' => $this->integer()->notNull(),
            'description' => $this->text(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->createIndex('idx_crm_l_item_id', '{{%crm_logs}}','item_id');
        $this->createIndex('idx_crm_l_entity', '{{%crm_logs}}','entity');
    }

    public function down()
    {
        $this->dropTable('{{%crm_logs}}');
    }
}
