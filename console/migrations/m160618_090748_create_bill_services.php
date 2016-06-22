<?php

use yii\db\Migration;

/**
 * Handles the creation for table `bill_services`.
 */
class m160618_090748_create_bill_services extends Migration
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

        $this->createTable('{{%bill_services}}',[
            'id' => $this->primaryKey(),
            'bill_id' => $this->integer()->notNull(),
            'service_id' => $this->integer()->notNull(),
            'serv_tpl_id' => $this->integer(),
            'amount' => $this->money(17,4)->notNull(),
            'serv_title' => $this->text()->notNull(),
            'description' => $this->text(),
            'offer' => $this->text(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ],$tableOptions);

        $this->createIndex('idx-bls-bill-id','{{%bill_services}}','bill_id');
        $this->createIndex('idx-bls-service-id','{{%bill_services}}','service_id');
        $this->createIndex('idx-bls-serv-tpl-id','{{%bill_services}}','serv_tpl_id');

        $this->addForeignKey('FK-bls-bill_id','{{%bill_services}}','bill_id','{{%bills}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-bls-service_id','{{%bill_services}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-bls-serv_tpl_id','{{%bill_services}}','serv_tpl_id','{{%bill_template}}','id','SET NULL','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-bls-bill_id','{{%bill_services}}');
        $this->dropForeignKey('FK-bls-service_id','{{%bill_services}}');
        $this->dropForeignKey('FK-bls-service_id','{{%bill_services}}');

        $this->dropTable('{{%bill_services}}');
    }
}
