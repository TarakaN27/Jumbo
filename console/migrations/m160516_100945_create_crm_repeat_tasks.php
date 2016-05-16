<?php

use yii\db\Migration;

/**
 * Handles the creation for table `crm_repeat_tasks`.
 */
class m160516_100945_create_crm_repeat_tasks extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%crm_task_repeat}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer(),
            'type' => $this->smallInteger(),
            'everyday' => $this->boolean(),
            'everyday_custom' => $this->boolean(),
            'everyday_value' => $this->boolean(),
            'day' => $this->smallInteger(),
            'month' => $this->smallInteger(),
            'monday' => $this->boolean()->defaultValue(0),
            'tuesday' => $this->boolean()->defaultValue(0),
            'wednesday' => $this->boolean()->defaultValue(0),
            'thursday' => $this->boolean()->defaultValue(0),
            'friday' => $this->boolean()->defaultValue(0),
            'saturday' => $this->boolean()->defaultValue(0),
            'sunday' => $this->boolean()->defaultValue(0),
            'number_of_item' => $this->smallInteger(),
            'start_date' => $this->integer(),
            'end_type' => $this->smallInteger(),
            'count_occurrences' => $this->smallInteger(),
            'end_date' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-crmtr-task-id','{{%crm_task_repeat}}','task_id');
        $this->addForeignKey('FK-crmtr-task_id','{{%crm_task_repeat}}','task_id','{{%crm_task}}','id','CASCADE','RESTRICT');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%crm_task_repeat}}');
    }
}
