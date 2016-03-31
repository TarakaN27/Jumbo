<?php

use yii\db\Migration;

class m160331_103701_create_table_bonus_scheme_except extends Migration
{
    /**
     *
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bonus_scheme_except_cuser}}', [
            'cuser_id' => $this->integer()->notNull(),
            'scheme_id' => $this->integer()->notNull(),
            'PRIMARY KEY (cuser_id,scheme_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_bsec_cuser_id", "{{%bonus_scheme_except_cuser}}", 'cuser_id', "{{%c_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_bsec_scheme_id", "{{%bonus_scheme_except_cuser}}", "scheme_id", "{{%bonus_scheme}}", "id", 'CASCADE','RESTRICT');
    }

    /**
     *
     */
    public function down()
    {
        $this->dropTable('{{%bonus_scheme_except_cuser}}');
    }
}
