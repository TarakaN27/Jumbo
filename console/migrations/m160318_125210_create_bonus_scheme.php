<?php

use yii\db\Migration;

class m160318_125210_create_bonus_scheme extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bonus_scheme}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'type' => $this->smallInteger(),
            'num_month' => $this->smallInteger(),
            'inactivity' => $this->smallInteger(),
            'grouping_type' => $this->smallInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ],$tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%bonus_scheme}}');
    }
}
