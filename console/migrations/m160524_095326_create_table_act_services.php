<?php

use yii\db\Migration;

/**
 * Handles the creation for table `table_act_services`.
 */
class m160524_095326_create_table_act_services extends Migration
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
        $this->createTable('{{%act_services}}', [
            'id' => $this->primaryKey(),
            'act_id' => $this->integer()->notNull(),
            'service_id' => $this->integer()->notNull(),
            'amount' => $this->money(17,5),
            'quantity' => $this->smallInteger()->defaultValue(1),
            'contract_date' => $this->integer(),
            'contract_number' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-acts-act_id','{{%act_services}}','act_id');
        $this->addForeignKey('FK-acts-act_id','{{%act_services}}','act_id','{{%acts}}','id','CASCADE','RESTRICT');

        $this->createIndex('idx-actsrv-serv_id','{{%act_services}}','service_id');
        $this->addForeignKey('FK-acts-serv_id','{{%act_services}}','service_id',"{{%services}}", "id", 'CASCADE','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('table_act_services');
    }
}
