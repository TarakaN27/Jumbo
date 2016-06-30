<?php

use yii\db\Migration;

class m160630_080949_b_user_payment_records extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%b_user_payment_records}}',[
            'id' => $this->primaryKey()->notNull(),
            'buser_id' => $this->integer()->notNull(),
            'amount' => $this->money(17,4),
            'record_date' => $this->date(),
            'is_record' => $this->boolean()->defaultValue(0),
            'record_num' => $this->smallInteger(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ],$tableOptions);

        $this->createIndex('idx-bupr-buser_id','{{%b_user_payment_records}}','buser_id');
        $this->addForeignKey('FK-bupr-buser_id','{{%b_user_payment_records}}','buser_id','{{%b_user}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK-bupr-buser_id','{{%b_user_payment_records}}');
        $this->dropTable('{{%b_user_payment_records}}');
    }
}
