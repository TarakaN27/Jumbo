<?php

use yii\db\Schema;
use yii\db\Migration;

class m151216_140956_create_table_crm_task extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        /**
         * Таблица ролей для пользователй CRM
         */
        $this->createTable('{{%crm_task}}', [
            'id' =>$this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'deadline' => $this->dateTime(),
            'priority' => $this->smallInteger(),
            'type' => $this->smallInteger(),
            'task_control'=>$this->boolean(),
            'parent_id' => $this->integer(), //FK crm_task
            'assigned_id' => $this->integer(), //FK b_user
            'created_by' => $this->integer(), //FK b_user
            'time_estimate' => $this->integer(),
            'status' => $this->smallInteger(),
            'date_start' => $this->integer(),
            'duration_fact' => $this->integer(),
            'closed_by' => $this->integer(), //FK b_user
            'closed_date' => $this->integer(),
            'cmp_id' => $this->integer(), //FK c_user
            'contact_id' => $this->integer(), //FK_crm_cmp_contact
            'dialog_id' => $this->integer(), //FK dialogs
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->addForeignKey('FK_crmt_parent_id','{{%crm_task}}','parent_id','{{%crm_task}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_crmt_assigned','{{%crm_task}}','assigned_id','{{%b_user}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_crmt_created','{{%crm_task}}','created_by','{{%b_user}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_crmt_closed','{{%crm_task}}','closed_by','{{%b_user}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_crmt_cmp_id','{{%crm_task}}','cmp_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_crmt_contact','{{%crm_task}}','contact_id','{{%crm_cmp_contacts}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_crmt_dialog','{{%crm_task}}','dialog_id','{{%dialogs}}','id','SET NULL','RESTRICT');


        $this->createTable('{{%crm_task_accomplices}}',[
            'task_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'buser_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (task_id,buser_id)'
        ]);

        $this->addForeignKey("FK_crmta_buser_id", "{{%crm_task_accomplices}}", "buser_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_crmta_task_id", "{{%crm_task_accomplices}}", "task_id", "{{%crm_task}}", "id", 'CASCADE','RESTRICT');

        $this->createTable('{{%crm_task_watcher}}',[
            'task_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'buser_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (task_id,buser_id)'
        ]);

        $this->addForeignKey("FK_crmtw_buser_id", "{{%crm_task_watcher}}", "buser_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_crmtw_task_id", "{{%crm_task_watcher}}", "task_id", "{{%crm_task}}", "id", 'CASCADE','RESTRICT');

        $this->createTable('{{%crm_task_log_time}}',[
            'id' => $this->primaryKey(),
            'task_id' => $this->integer(),
            'buser_id' => $this->integer(),
            'spend_time' => $this->integer(),
            'description' => $this->text(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer()
        ]);

        $this->addForeignKey('FK_crmtlt_task_id','{{%crm_task_log_time}}','task_id','{{%crm_task}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_crmtlt_buser_id','{{%crm_task_log_time}}','buser_id', "{{%b_user}}", "id", 'SET NULL','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_crmtlt_task_id','{{%crm_task_log_time}}');
        $this->dropForeignKey('FK_crmtlt_buser_id','{{%crm_task_log_time}}');
        $this->dropTable('{{%crm_task_log_time}}');

        $this->dropForeignKey("FK_crmta_buser_id", "{{%crm_task_accomplices}}");
        $this->dropForeignKey("FK_crmta_task_id", "{{%crm_task_accomplices}}");
        $this->dropTable('{{%crm_task_accomplices}}');

        $this->dropForeignKey('FK_crmt_dialog','{{%crm_task}}');
        $this->dropForeignKey('FK_crmt_contact','{{%crm_task}}');
        $this->dropForeignKey('FK_crmt_cmp_id','{{%crm_task}}');
        $this->dropForeignKey('FK_crmt_closed','{{%crm_task}}');
        $this->dropForeignKey('FK_crmt_created','{{%crm_task}}');
        $this->dropForeignKey('FK_crmt_assigned','{{%crm_task}}');
        $this->dropForeignKey('FK_crmt_parent_id','{{%crm_task}}');

        $this->dropTable('{{%crm_task}}');
    }


}
