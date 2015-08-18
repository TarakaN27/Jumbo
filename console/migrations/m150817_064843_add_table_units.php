<?php

use yii\db\Schema;
use yii\db\Migration;

class m150817_064843_add_table_units extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%units}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING.' NOT NULL ',
            'type' => Schema::TYPE_SMALLINT,
            'service_id' => Schema::TYPE_INTEGER,
            'cost' => Schema::TYPE_INTEGER. ' NOT NULL ',
            'cuser_id' => Schema::TYPE_INTEGER,
            'multiple' => Schema::TYPE_BOOLEAN.' DEFAULT 0 ',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->createIndex('name','{{%units}}','name',TRUE);
        $this->createIndex('service_id','{{%units}}','service_id');
        $this->createIndex('cuser_id','{{%units}}','cuser_id');
        $this->addForeignKey("FK_unit_service_id", "{{%units}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_unit_cuser_id", "{{%units}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');

        $this->createTable('{{%units_cost_history}}', [
            'id' => Schema::TYPE_PK,
            'unit_id' => Schema::TYPE_INTEGER. ' NOT NULL ',
            'date' => Schema::TYPE_DATE. ' NOT NULL ',
            'old_cost' => Schema::TYPE_INTEGER. ' NOT NULL ',
            'new_cost' => Schema::TYPE_INTEGER. ' NOT NULL ',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->createIndex('unit_id','{{%units_cost_history}}','unit_id');
        $this->addForeignKey("FK_unit_cs_h_unit_id", "{{%units_cost_history}}", 'unit_id', "{{%units}}", "id", 'CASCADE','RESTRICT');

        $this->createTable('{{%units_to_manager}}', [
            'id' => Schema::TYPE_PK ,
            'unit_id' => Schema::TYPE_INTEGER. ' NOT NULL ' ,
            'cost' => Schema::TYPE_INTEGER. ' NOT NULL ',
            'manager_id' => Schema::TYPE_INTEGER. ' NOT NULL ',
            'payment_id' => Schema::TYPE_INTEGER,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->createIndex('manager_id','{{%units_to_manager}}','manager_id');
        $this->createIndex('payment_id','{{%units_to_manager}}','payment_id');
        $this->createIndex('unit_id','{{%units_to_manager}}','unit_id');

        $this->addForeignKey("FK_unit_man_unit_id", "{{%units_to_manager}}", 'unit_id', "{{%units}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_unit_man_manager_id", "{{%units_to_manager}}", 'manager_id', "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_unit_man_payment_idd", "{{%units_to_manager}}", 'payment_id', "{{%payments}}", "id", 'CASCADE','RESTRICT');

    }

    public function down()
    {
        $this->dropTable('{{%units}}');
        $this->dropTable('{{%units_cost_history}}');
        $this->dropTable('{{%units_to_manager}}');
    }
}
