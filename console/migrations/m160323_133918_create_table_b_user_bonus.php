<?php

use yii\db\Migration;
use backend\models\BUser;

class m160323_133918_create_table_b_user_bonus extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%b_user_bonus}}', [
            'id' => $this->primaryKey(),
            'amount' => $this->money(4),
            'buser_id' => $this->integer(),
            'scheme_id' => $this->integer(),
            'payment_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);

        $this->createIndex('bub_buser_id','{{%b_user_bonus}}','buser_id');
        $this->createIndex('bub_scheme_id','{{%b_user_bonus}}','scheme_id');
        $this->createIndex('bub_payment_id','{{%b_user_bonus}}','payment_id');

        $this->addForeignKey('FK_bub_buser_id','{{%b_user_bonus}}','buser_id','{{%b_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_bub_scheme_id','{{%b_user_bonus}}','scheme_id','{{%bonus_scheme}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_bub_payment_id','{{%b_user_bonus}}','payment_id','{{%payments}}','id','CASCADE','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%b_user_bonus}}');
    }
}
