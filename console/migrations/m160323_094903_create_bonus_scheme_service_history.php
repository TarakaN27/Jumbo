<?php

use yii\db\Migration;

class m160323_094903_create_bonus_scheme_service_history extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bonus_scheme_service_history}}', [
            'id' => $this->primaryKey(),
            'scheme_id' => $this->integer(),
            'service_id' => $this->integer(),
            'month_percent' => $this->text(),
            'cost' => $this->money(4),
            'unit_multiple' => $this->boolean(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);

        $this->addForeignKey('FK_bssh_scheme_id','{{%bonus_scheme_service_history}}','scheme_id','{{%bonus_scheme}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_bssh_service_id','{{%bonus_scheme_service_history}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropTable('{{%bonus_scheme_service_history}}');
    }
}
