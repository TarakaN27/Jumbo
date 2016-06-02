<?php

use yii\db\Migration;

/**
 * Handles the creation for table `implicit_act_payment`.
 */
class m160602_122935_create_implicit_act_payment extends Migration
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
        $this->createTable('{{%act_implicit_payment}}', [
            'id' => $this->primaryKey(),
            'act_id' => $this->integer(),
            'payment_id' => $this->integer(),
            'service_id' => $this->integer(),
            'amount' => $this->money(17,5),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);


        $this->createIndex('idx-actft-act_id','{{%act_implicit_payment}}','act_id');
        $this->createIndex('idx-actft-pay_id','{{%act_implicit_payment}}','payment_id');
        $this->createIndex('idx-actft-srv_id','{{%act_implicit_payment}}','service_id');

        $this->addForeignKey('FK-actft-act_id','{{%act_implicit_payment}}','act_id','{{%acts}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-actft-pay_id','{{%act_implicit_payment}}','payment_id',"{{%payments}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey('FK-actft-srv_id','{{%act_implicit_payment}}','service_id',"{{%services}}", "id", 'CASCADE','RESTRICT');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-actft-act_id','{{%act_field_template}}');
        $this->dropForeignKey('FK-actft-pay_id','{{%act_field_template}}');
        $this->dropForeignKey('FK-actft-srv_id','{{%act_field_template}}');

        $this->dropTable('{{%act_implicit_payment}}');
    }
}
