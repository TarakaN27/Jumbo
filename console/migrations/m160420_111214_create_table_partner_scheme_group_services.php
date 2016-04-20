<?php

use yii\db\Migration;

class m160420_111214_create_table_partner_scheme_group_services extends Migration
{
    /**
     *
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_schemes_services_group}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->addColumn('{{%partner_schemes_services_history}}','group_id',$this->integer());
        $this->addColumn('{{%partner_schemes_services}}','group_id',$this->integer());

        $this->addForeignKey('FK-pssh-group_id','{{%partner_schemes_services_history}}','group_id','{{%partner_schemes_services_group}}','id','SET NULL','RESTRICT');
        $this->addForeignKey('FK-pss-group_id','{{%partner_schemes_services}}','group_id','{{%partner_schemes_services_group}}','id','SET NULL','RESTRICT');
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK-pssh-group_id','{{%partner_schemes_services_history}}');
        $this->dropForeignKey('FK-pss-group_id','{{%partner_schemes_services}}');
        $this->dropColumn('{{%partner_schemes_services_history}}','group_id');
        $this->dropColumn('{{%partner_schemes_services}}','group_id');
        $this->dropTable('{{%partner_schemes_services_group}}');
    }
}
