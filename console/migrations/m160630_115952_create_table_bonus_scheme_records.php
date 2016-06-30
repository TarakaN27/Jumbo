<?php

use yii\db\Migration;

/**
 * Handles the creation for table `table_bonus_scheme_records`.
 */
class m160630_115952_create_table_bonus_scheme_records extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%bonus_scheme_records}}',[
            'id' => $this->primaryKey()->notNull(),
            'scheme_id' => $this->integer()->notNull(),
            'params' => $this->text(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ],$tableOptions);

        $this->createIndex('idx-bsr-scheme_id','{{%bonus_scheme_records}}','scheme_id');
        $this->addForeignKey('FK-bsr-scheme_id','{{%bonus_scheme_records}}','scheme_id','{{%bonus_scheme}}','id','CASCADE','RESTRICT');

        $this->createTable('{{%bonus_scheme_records_history}}',[
            'id' => $this->primaryKey()->notNull(),
            'scheme_id' => $this->integer()->notNull(),
            'params' => $this->text(),
            'update_date' => $this->date(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ],$tableOptions);

        $this->createIndex('idx-bsrh-scheme_id','{{%bonus_scheme_records_history}}','scheme_id');
        $this->addForeignKey('FK-bsrh-scheme_id','{{%bonus_scheme_records_history}}','scheme_id','{{%bonus_scheme}}','id','CASCADE','RESTRICT');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%bonus_scheme_records_history}}');
        $this->dropTable('{{%bonus_scheme_records}}');
    }
}
