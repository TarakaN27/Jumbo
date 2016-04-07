<?php

use yii\db\Migration;

class m160324_082208_create_table_payments_sale extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%payments_sale}}', [
            'id' => $this->primaryKey(),
            'cuser_id' => $this->integer(),
            'service_id' => $this->integer(),
            'buser_id' => $this->integer(),
            'sale_date' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);


        $this->createIndex('id_ps_cuser_id','{{%payments_sale}}','cuser_id');
        $this->createIndex('id_ps_service_id','{{%payments_sale}}','service_id');
        $this->createIndex('id_ps_buser_id','{{%payments_sale}}','buser_id');

        $this->addForeignKey('FK_ps_cuser_id','{{%payments_sale}}','cuser_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_ps_service_id','{{%payments_sale}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_ps_buser_id','{{%payments_sale}}','buser_id','{{%b_user}}','id','SET NULL','RESTRICT');


    }

    public function safeDown()
    {
        $this->dropTable('{{%payments_sale}}');
    }
}
