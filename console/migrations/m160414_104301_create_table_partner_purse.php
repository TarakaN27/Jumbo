<?php

use yii\db\Migration;

class m160414_104301_create_table_partner_purse extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_purse}}', [
            'id' => $this->primaryKey(),
            'cuser_id' => $this->integer()->notNull()->unique(),
            'amount' => $this->money(17,4),
            'withdrawal' => $this->money(17,4),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-parpcuserid','{{%partner_purse}}','cuser_id');
        $this->addForeignKey('FK_parp_cuser_id','{{%partner_purse}}','cuser_id','{{%c_user}}','id','CASCADE','RESTRICT');
    }
    public function down()
    {
        $this->dropTable('{{%partner_purse}}');
    }
}
