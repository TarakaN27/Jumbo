<?php

use yii\db\Migration;

class m160419_095448_create_table_partner_scheme_serv_history extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_schemes_services_history}}', [
            'id' => $this->primaryKey(),
            'scheme_id' => $this->integer()->notNull(),
            'service_id' => $this->integer()->notNull(),
            'ranges' => $this->text(),
            'legal' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-pssh-serv_id','{{%partner_schemes_services_history}}','service_id');
        $this->createIndex('idx-pssh-schem_id','{{%partner_schemes_services_history}}','scheme_id');

        $this->addForeignKey('FK_pashserhschemeid','{{%partner_schemes_services_history}}','scheme_id','{{%partner_schemes}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK_pashserhservid','{{%partner_schemes_services_history}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
    }

    public function safeDown()
    {
        $this->dropForeignKey('FK_pashserhschemeid','{{%partner_schemes_services_history}}');
        $this->dropForeignKey('FK_pashserhservid','{{%partner_schemes_services_history}}');

        $this->dropIndex('idx-pssh-serv_id','{{%partner_schemes_services_history}}');
        $this->dropIndex('idx-pssh-schem_id','{{%partner_schemes_services_history}}');

        $this->dropTable('{{%partner_schemes_services_history}}');
    }
}
