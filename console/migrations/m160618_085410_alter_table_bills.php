<?php

use yii\db\Migration;

class m160618_085410_alter_table_bills extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%bills}}','service_id',$this->integer());
        $this->alterColumn('{{%bills}}','amount',$this->money(17,4));
        $this->alterColumn('{{%bills}}','object_text',$this->text());
    }

    public function down()
    {
        $this->alterColumn('{{%bills}}','service_id',$this->integer()->notNull());
        $this->alterColumn('{{%bills}}','amount',$this->integer());
        $this->alterColumn('{{%bills}}','object_text',$this->text()->notNull());
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
