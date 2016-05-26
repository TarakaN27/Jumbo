<?php

use yii\db\Migration;

/**
 * Handles the creation for table `table_acts_to_paymenas`.
 */
class m160524_103401_create_table_acts_to_paymenas extends Migration
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
        $this->createTable('{{%act_to_payments}}', [
            'id' => $this->primaryKey(),
            'act_id' => $this->integer()->notNull(),
            'payment_id' => $this->integer()->notNull(),
            'amount' => $this->money(17,5),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-actp-act_id','{{%act_to_payments}}','act_id');
        $this->addForeignKey('FK-actp-act_id','{{%act_to_payments}}','act_id','{{%acts}}','id','CASCADE','RESTRICT');

        $this->createIndex('idx-actpay-payment_id','{{%act_to_payments}}','payment_id');
        $this->addForeignKey('FK-actpay-payment_id','{{%act_to_payments}}','payment_id',"{{%payments}}", "id", 'CASCADE','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-actp-act_id','{{%act_to_payments}}');
        $this->dropForeignKey('FK-acts-serv_id','{{%act_to_payments}}');
        $this->dropTable('{{%act_to_payments}}');
    }
}
