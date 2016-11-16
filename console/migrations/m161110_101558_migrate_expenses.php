<?php

use yii\db\Migration;
use yii\db\Schema;
class m161110_101558_migrate_expenses extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->addColumn('{{%expense}}','id_1c', $this->char(25));
        $this->createIndex('idx-id_1c','{{%expense}}','id_1c');

        $this->createTable('{{%expense_1c_link}}',[
            'cuser_id' => Schema::TYPE_INTEGER,
            'category_1c_id' => Schema::TYPE_INTEGER,
            'jumbo_category_id' => Schema::TYPE_INTEGER,
            'count' => Schema::TYPE_INTEGER.' NOT NULL',
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);
        $this->addPrimaryKey('news-cate_pk', '{{%expense_1c_link}}', ['cuser_id', 'category_1c_id','jumbo_category_id']);

        $this->createTable('{{%expense_1c_categories}}',[
            'id' => Schema::TYPE_PK,
            'name' => $this->char(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);
    }

    public function down()
    {
        $this->dropTable("{{%expense_1c_link}}");
        $this->dropTable("{{%expense_1c_categories}}");
        $this->dropColumn('{{%expense}}','id_1c');
        return true;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
