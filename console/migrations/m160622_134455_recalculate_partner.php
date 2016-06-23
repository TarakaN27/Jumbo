<?php

use yii\db\Migration;

class m160622_134455_recalculate_partner extends Migration
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

        $this->createTable('{{%recalculate_partner}}',[
            'id' => $this->primaryKey(),
            'cuser_id' => $this->integer()->notNull(),
            'begin_date' => $this->date(),
            'payment_id' => $this->integer(),
            'created_at' => $this->integer() ,
            'updated_at' => $this->integer() ,
        ],$tableOptions);

        $this->createIndex('idx-rec-part-cuserid','{{%recalculate_partner}}','cuser_id');
        $this->createIndex('idx-rec-part-payid','{{%recalculate_partner}}','payment_id');

        $this->addForeignKey('FK-rec-part-cuser_id','{{%recalculate_partner}}','cuser_id','{{%c_user}}','id','CASCADE','RESTRICT');
        $this->addForeignKey('FK-rec-part-pay_id','{{%recalculate_partner}}','payment_id','{{%payments}}','id','CASCADE','RESTRICT');
    }

    /**
     *
     */
    public function down()
    {
        $this->dropForeignKey('FK-rec-part-cuser_id','{{%recalculate_partner}}');
        $this->dropForeignKey('FK-rec-part-pay_id','{{%recalculate_partner}}');
        $this->dropTable('{{%recalculate_partner}}');
    }
}
