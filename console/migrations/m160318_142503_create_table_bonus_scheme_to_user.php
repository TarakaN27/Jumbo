<?php

use yii\db\Migration;

class m160318_142503_create_table_bonus_scheme_to_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bonus_scheme_to_buser}}', [
            'buser_id' => $this->integer()->notNull(),// Schema::TYPE_INTEGER. ' NOT NULL',
            'scheme_id' => $this->integer()->notNull(),//Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (buser_id,scheme_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_bstb_buser_id", "{{%bonus_scheme_to_buser}}", 'buser_id', "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_bstb_scheme_id", "{{%bonus_scheme_to_buser}}", "scheme_id", "{{%bonus_scheme}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%bonus_scheme_to_buser}}');
    }
}
