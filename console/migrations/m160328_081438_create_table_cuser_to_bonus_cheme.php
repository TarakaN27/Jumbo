<?php

use yii\db\Migration;

class m160328_081438_create_table_cuser_to_bonus_cheme extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bonus_scheme_to_cuser}}', [
            'cuser_id' => $this->integer()->notNull(),// Schema::TYPE_INTEGER. ' NOT NULL',
            'scheme_id' => $this->integer()->notNull(),//Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (cuser_id,scheme_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_bstc_cuser_id", "{{%bonus_scheme_to_cuser}}", 'cuser_id', "{{%c_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_bstc_scheme_id", "{{%bonus_scheme_to_cuser}}", "scheme_id", "{{%bonus_scheme}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%bonus_scheme_to_cuser}}');
    }
}
