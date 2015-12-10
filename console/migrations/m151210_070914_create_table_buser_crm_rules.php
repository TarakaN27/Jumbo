<?php

use yii\db\Schema;
use yii\db\Migration;

class m151210_070914_create_table_buser_crm_rules extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        /**
         * Таблица ролей для пользователй CRM
         */
        $this->createTable('{{%b_user_crm_roles}}', [
            'id' =>$this->primaryKey(),
            'name' => $this->string()->notNull(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        /**
         * Таблица правил для ролей CRM
         */
        $this->createTable('{{%b_user_crm_rules}}', [
            'id' =>$this->primaryKey(),
            'role_id' => $this->integer(),
            'entity' => $this->string()->notNull(),
            'crt' => $this->smallInteger(),
            'rd' => $this->smallInteger(),
            'upd' => $this->smallInteger(),
            'del' => $this->smallInteger(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);


        $this->addForeignKey('FK_bucr_rule_id','{{%b_user_crm_rules}}','role_id','{{%b_user_crm_roles}}','id','CASCADE','RESTRICT');

        /**
         * Таблица групп для пользователей CRM
         */
        $this->createTable('{{%b_user_crm_group}}', [
            'id' =>$this->primaryKey(),
            'name' => $this->string()->notNull(),
            'role_id' => $this->integer(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->addForeignKey('FK_bucg_role_id','{{%b_user_crm_group}}','role_id','{{%b_user_crm_roles}}','id','CASCADE','RESTRICT');
    }




    public function down()
    {
        $this->dropForeignKey('FK_bucg_role_id','{{%b_user_crm_group}}');
        $this->dropTable('{{%b_user_crm_group}}');
        $this->dropForeignKey('FK_bucr_rule_id','{{%b_user_crm_roles}}');
        $this->dropTable('{{%b_user_crm_roles}}');
        $this->dropTable('{{%b_user_crm_rules}}');
    }


}
