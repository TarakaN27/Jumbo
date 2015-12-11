<?php

use yii\db\Schema;
use yii\db\Migration;

class m151211_075351_create_table_crm_cmp_contacts extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%crm_cmp_contacts}}', [
            'id' =>$this->primaryKey(),
            'cmp_id' => $this->integer(),
            'fio' => $this->string()->notNull(),
            'type' => $this->smallInteger(),
            'post' => $this->string(),
            'description' => $this->text(),
            'addition_info' => $this->text(),
            'assigned_at' => $this->integer(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->createIndex('idx_bucr_cmp_id', '{{%crm_cmp_contacts}}','cmp_id');
        $this->createIndex('idx_bucr_assigned_at', '{{%crm_cmp_contacts}}','assigned_at');
        $this->addForeignKey('FK_bucr_cmp_id','{{%crm_cmp_contacts}}','cmp_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_bucr_assigned_at','{{%crm_cmp_contacts}}','assigned_at','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_bucr_cmp_id','{{%crm_cmp_contacts}}');
        $this->dropForeignKey('FK_bucr_assigned_at','{{%crm_cmp_contacts}}');
        $this->dropTable('{{%crm_cmp_contacts}}');
    }

}
