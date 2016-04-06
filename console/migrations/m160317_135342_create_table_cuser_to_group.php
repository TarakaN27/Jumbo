<?php

use yii\db\Migration;

class m160317_135342_create_table_cuser_to_group extends Migration
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

        $this->createTable('{{%cuser_to_group}}', [
            'cuser_id' => $this->integer()->notNull(),// Schema::TYPE_INTEGER. ' NOT NULL',
            'group_id' => $this->integer()->notNull(),//Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (cuser_id,group_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_ctg_cuser_id", "{{%cuser_to_group}}", "cuser_id", "{{%c_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_ctg_group_id", "{{%cuser_to_group}}", "group_id", "{{%c_user_groups}}", "id", 'CASCADE','RESTRICT');
    }

    /**
     *
     */
    public function safeDown()
    {
        $this->dropForeignKey("FK_ctg_cuser_id", "{{%cuser_to_group}}");
        $this->dropForeignKey("FK_ctg_group_id", "{{%cuser_to_group}}");
        $this->dropTable('{{%cuser_to_group}}');
    }
}
