<?php

use yii\db\Migration;

/**
 * Handles the creation for table `table_act_field_template`.
 */
class m160525_142025_create_table_act_field_template extends Migration
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
        $this->createTable('{{%act_field_template}}', [
            'id' => $this->primaryKey(),
            'service_id' => $this->integer()->notNull(),
            'legal_id' => $this->integer()->notNull(),
            'job_name' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->createIndex('idx-acft-service_id','{{%act_field_template}}','service_id');
        $this->createIndex('idx-acft-legal_id','{{%act_field_template}}','legal_id');

        $this->addForeignKey('FK-acft-service_id','{{%act_field_template}}','service_id','{{%services}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-acft-legal_id','{{%act_field_template}}','legal_id','{{%legal_person}}','id','CASCADE','RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('FK-acft-legal_id','{{%act_field_template}}');
        $this->dropForeignKey('FK-acft-service_id','{{%act_field_template}}');
        $this->dropTable('{{%act_field_template}}');
    }
}
