<?php

use yii\db\Schema;
use yii\db\Migration;

class m150729_130806_add_table_payment_condition extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%payment_condition}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'description' => Schema::TYPE_TEXT . ' NOT NULL',
            'service_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'l_person_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'is_resident' => Schema::TYPE_BOOLEAN . ' DEFAULT 0',
            'summ_from' => Schema::TYPE_DECIMAL . ' (19,4) NOT NULL',
            'summ_to' => Schema::TYPE_DECIMAL . '(19,4) NOT NULL',
            'corr_factor' => Schema::TYPE_DECIMAL . ' (19,10) NOT NULL',
            'commission' => Schema::TYPE_DECIMAL . ' (19,10) DEFAULT 0',
            'sale' => Schema::TYPE_DECIMAL . ' (19,10) NOT NULL',
            'tax' => Schema::TYPE_DECIMAL . ' (19,10) DEFAULT 0',

            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("FK_pc_l_person_id", "{{%payment_condition}}", "l_person_id", "{{%legal_person}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_pc_service_id", "{{%payment_condition}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%payment_condition}}');
    }
}
