<?php

use yii\db\Migration;

class m160426_084132_create_table_partner_withdrawal_categories_link extends Migration
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

        $this->createTable('{{%partner_expense_cat_link}}', [
            'id' => $this->primaryKey(),
            'type' => $this->smallInteger(),
            'legal_person_id' => $this->integer(),
            'service_id' => $this->integer(),
            'expanse_cat_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);
        
        $this->createIndex('idx-pwcl-lpid','{{%partner_expense_cat_link}}','legal_person_id');
        $this->createIndex('idx-pwcl-service_id','{{%partner_expense_cat_link}}','service_id');
        $this->createIndex('idx-pwcl-expcatid','{{%partner_expense_cat_link}}','expanse_cat_id');
        
        $this->addForeignKey('FK-pwcl-legal_person','{{%partner_expense_cat_link}}','legal_person_id','{{%legal_person}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwcl-serv_id','{{%partner_expense_cat_link}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-pwcl-exp_cat_id','{{%partner_expense_cat_link}}','expanse_cat_id',"{{%expense_categories}}",'id','CASCADE','RESTRICT');
        
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK-pwcl-legal_person','{{%partner_expense_cat_link}}');
        $this->dropForeignKey('FK-pwcl-serv_id','{{%partner_expense_cat_link}}');
        $this->dropForeignKey('FK-pwcl-exp_cat_id','{{%partner_expense_cat_link}}');

        $this->dropIndex('idx-pwcl-lpid','{{%partner_expense_cat_link}}');
        $this->dropIndex('idx-pwcl-service_id','{{%partner_expense_cat_link}}');
        $this->dropIndex('idx-pwcl-expcatid','{{%partner_expense_cat_link}}');
        $this->dropTable('table_partner_withdrawal_categories_link');
    }
}
