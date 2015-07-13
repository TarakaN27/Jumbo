<?php

use yii\db\Schema;
use yii\db\Migration;

class m150713_063305_create_table_invite_code extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%buser_invite_code}}', [

            'id' => Schema::TYPE_PK,
            'code' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING ,
            'user_type' => Schema::TYPE_SMALLINT ,
            'buser_id' => Schema::TYPE_INTEGER ,
            'status' => Schema::TYPE_BOOLEAN ,
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,

        ], $tableOptions);

        $this->createIndex('idx_buser_inv_code', '{{%buser_invite_code}}', 'code');
        $this->createIndex('idx_buser_inv_buser_id', '{{%buser_invite_code}}', 'buser_id');
        $this->createIndex('idx_buser_inv_email', '{{%buser_invite_code}}', 'email');
    }

    public function down()
    {
        $this->dropTable('{{%buser_invite_code}}');
    }

}
