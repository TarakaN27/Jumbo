<?php

use yii\db\Schema;
use yii\db\Migration;

class m151123_071839_create_table_addition_fileds extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        /**
         * Таблица с дополнительными полями
         */
        $this->createTable('{{%entity_fields}}', [
            'id' =>$this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'alias' => $this->string()->notNull()->unique(),
            'entity' => $this->string()->notNull(),
            'type' => $this->integer()->notNull()->defaultValue(0),
            'required' => $this->boolean(),
            'validate' => $this->smallInteger(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);
        /**
         * Таблица со значениями доп. полей модели
         */
        $this->createTable('{{%entity_fields_value}}', [
            'id' =>$this->primaryKey(),
            'entity' => $this->string()->notNull(),
            'item_id' => $this->integer()->notNull(),
            'field_id' => $this->integer()->notNull(),
            'value' => $this->text()->notNull(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ], $tableOptions);

        $this->addForeignKey('FK_efv_field_id','{{%entity_fields_value}}','field_id','{{%entity_fields}}','id', 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_efv_field_id','{{%entity_fields_value}}');
        $this->dropTable('{{%entity_fields_value}}');
        $this->dropTable('{{%entity_fields}}');
    }
}
