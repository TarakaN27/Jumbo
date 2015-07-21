<?php

use yii\db\Schema;
use yii\db\Migration;

class m150715_132216_add_messanger_tables extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%dialogs}}', [
            'id' => Schema::TYPE_PK,
            'buser_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'status' => Schema::TYPE_SMALLINT . ' DEFAULT 0',
            'theme' => Schema::TYPE_TEXT.' NOT NULL ',
            'type' => Schema::TYPE_SMALLINT . ' DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("owner", "{{%dialogs}}", "buser_id", "{{%b_user}}", "id", 'CASCADE','CASCADE');
        $this->createIndex('idx_dialogs_buser_id', '{{%dialogs}}', 'buser_id');

        $this->createTable('{{%messages}}', [
            'id' => Schema::TYPE_PK,
            'msg' => Schema::TYPE_TEXT . ' NOT NULL',
            'parent_id' => Schema::TYPE_INTEGER ,
            'lvl' => Schema::TYPE_SMALLINT ,
            'buser_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'dialog_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'status' => Schema::TYPE_SMALLINT . ' DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);

        $this->addForeignKey("FK_message_owner", "{{%messages}}", "buser_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_message_dialog", "{{%messages}}", "dialog_id", "{{%dialogs}}", "id", 'CASCADE','RESTRICT');
        $this->createIndex('idx_messages_buser_id', '{{%messages}}', 'buser_id');
        $this->createIndex('idx_messages_dialog_id', '{{%messages}}', 'dialog_id');

        $this->createTable('{{%buser_to_dialogs}}', [
            'buser_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'dialog_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (buser_id,dialog_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_dialog_user", "{{%buser_to_dialogs}}", "buser_id", "{{%b_user}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_user_dialog", "{{%buser_to_dialogs}}", "dialog_id", "{{%dialogs}}", "id", 'CASCADE','RESTRICT');

        $this->createTable('{{%files_to_messages}}', [
            'file_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'message_id' => Schema::TYPE_INTEGER. ' NOT NULL',
            'PRIMARY KEY (file_id,message_id)'
        ], $tableOptions);

        $this->addForeignKey("FK_messages_file", "{{%files_to_messages}}", "file_id", "{{%files}}", "id", 'CASCADE','RESTRICT');
        $this->addForeignKey("FK_file_message", "{{%files_to_messages}}", "message_id", "{{%messages}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%files_to_messages}}');
        $this->dropTable('{{%buser_to_dialogs}}');
        $this->dropTable('{{%messages}}');
        $this->dropTable('{{%dialogs}}');
    }

}
