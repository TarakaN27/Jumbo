<?php

use yii\db\Migration;

/**
 * Handles the creation for table `recalculate_table`.
 */
class m160705_134708_create_recalculate_table extends Migration
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

        $this->createTable('{{%recalculations}}',[
            'id' => $this->primaryKey()->notNull(),
            'buser_id' => $this->integer(),
            'payment_id' => $this->integer(),
            'begin_date' => $this->date(),
            'type' => $this->smallInteger()->notNull(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ],$tableOptions);

        $this->createIndex('idx-rclc-buser_id','{{%recalculations}}','buser_id');
        $this->createIndex('idx-rclc-payment_id','{{%recalculations}}','payment_id');

        $this->addForeignKey('FK-rclc-buser_id','{{%recalculations}}','buser_id','{{%b_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-rclc-payment_id','{{%recalculations}}','payment_id','{{%payments}}','id','CASCADE','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%recalculations}}');
    }
}
