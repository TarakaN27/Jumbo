<?php

use yii\db\Schema;
use yii\db\Migration;

class m151211_145241_create_crm_cmp_file extends Migration
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
        $this->createTable('{{%crm_cmp_file}}', [
            'id' =>$this->primaryKey(),
            'cmp_id' => $this->integer(),
            'name' => $this->string(),
            'ext' => $this->string(),
            'src' => $this->string(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->addForeignKey('FK_ccf_cmp_id','{{%crm_cmp_file}}','cmp_id','{{%c_user}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_ccf_cmp_id','{{%crm_cmp_file}}');
        $this->dropTable('{{%crm_cmp_file}}');
    }


}
