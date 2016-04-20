<?php

use yii\db\Migration;

class m160418_135446_add_column_cuser extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_schemes}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'start_period' => $this->smallInteger(),
            'regular_period' => $this->smallInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createTable('{{%partner_schemes_services}}', [
            'id' => $this->primaryKey(),
            'scheme_id' => $this->integer()->notNull(),
            'service_id' => $this->integer()->notNull(),
            'ranges' => $this->text(),
            'legal' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->addColumn('{{%c_user}}','partner_scheme',$this->integer());

        $this->createIndex('idx-cu-p_scheme','{{%c_user}}','partner_scheme');
        $this->createIndex('idx-pss-serv_id','{{%partner_schemes_services}}','service_id');
        $this->createIndex('idx-pss-schem_id','{{%partner_schemes_services}}','scheme_id');

        $this->addForeignKey('FK_cuparsche','{{%c_user}}','partner_scheme','{{%partner_schemes}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK_pashserschemeid','{{%partner_schemes_services}}','scheme_id','{{%partner_schemes}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_pashserservid','{{%partner_schemes_services}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_cuparsche','{{%c_user}}');
        $this->dropForeignKey('FK_pashserschemeid','{{%partner_schemes_services}}');
        $this->dropForeignKey('FK_pashserservid','{{%partner_schemes_services}}');

        $this->dropIndex('idx-cu-p_scheme','{{%c_user}}');
        $this->dropIndex('idx-pss-serv_id','{{%partner_schemes_services}}');
        $this->dropIndex('idx-pss-schem_id','{{%partner_schemes_services}}');

        $this->dropColumn('{{%c_user}}','partner_scheme');
        $this->dropTable('{{%partner_schemes_services}}');
        $this->dropTable('{{%partner_schemes}}');
    }

}
