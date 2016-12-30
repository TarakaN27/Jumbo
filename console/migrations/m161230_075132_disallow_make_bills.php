<?php

use yii\db\Migration;

class m161230_075132_disallow_make_bills extends Migration
{
    public function up()
    {
        $this->addColumn('{{%legal_person}}','disallow_create_bill', $this->integer(1));
        \common\models\LegalPerson::updateAll(['disallow_create_bill'=>0]);
    }

    public function down()
    {

        $this->dropColumn('{{%legal_person}}','disallow_create_bill');
        return true;
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
