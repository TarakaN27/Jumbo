<?php

use yii\db\Migration;

class m160318_140259_create_table_bonus_scheme_service extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bonus_scheme_service}}', [
            'id' => $this->primaryKey(),
            'scheme_id' => $this->integer(),
            'service_id' => $this->integer(),
            'month_percent' => $this->text(),
            'cost' => $this->money(4),
            'unit_multiple' => $this->boolean(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);

        $this->addForeignKey('FK_bss_scheme_id','{{%bonus_scheme_service}}','scheme_id','{{%bonus_scheme}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_bss_service_id','{{%bonus_scheme_service}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%bonus_scheme_service}}');
    }
}
