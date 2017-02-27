<?php

use yii\db\Migration;

class m170224_100115_bank_details extends Migration
{
    public function up()
    {
        $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%bank_details}}', [
            'id' => $this->primaryKey(),
            'name' => $this->char(255),
            'legal_person_id' => $this->integer(),
            'bank_details' => $this->text(),
            'bill_hint' => $this->text(),
            'status' => $this->integer(2),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],$tableOptions);

        $this->addColumn('{{%legal_person}}','default_bank_id', $this->integer());

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%cuser_link_bank_details}}', [
            'id' => $this->primaryKey(),
            'cuser_id' => $this->integer(),
            'bank_details_id' => $this->integer(),
            'legal_person_id' => $this->integer(),
        ],$tableOptions);

        $this->addColumn('{{%legal_person}}','bank_details_id', $this->integer());

        $this->addColumn('{{%acts}}','bank_id', $this->integer());

        $this->addColumn('{{%payment_request}}','bank_id', $this->integer());

        $this->addColumn('{{%partner_w_bookkeeper_request}}','bank_id', $this->integer());

        $legalPerson = \common\models\LegalPerson::find()->where(['disallow_create_bill'=>0])->orderBy(['id' => SORT_ASC])->all();
        foreach($legalPerson as $item){
            $bankDetails = new \common\models\BankDetails();
            $bankDetails->name = 'МТБанк';
            $bankDetails->legal_person_id = $item->id;
            $bankDetails->status = 1;
            $bankDetails->bank_details = $item->doc_requisites;
            $bankDetails->save();
            $item->default_bank_id = $bankDetails->id;
            $item->save();
            \common\models\PaymentRequest::updateAll(['bank_id'=>$item->default_bank_id],['legal_id'=>$item->id]);
            \common\models\Acts::updateAll(['bank_id'=>$item->default_bank_id],['lp_id'=>$item->id]);
            \common\models\PartnerWBookkeeperRequest::updateAll(['bank_id'=>$item->default_bank_id],['legal_id'=>$item->id]);
        }
        $this->dropColumn('{{%legal_person}}','doc_requisites');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%bank_details}}');
        $this->dropTable('{{%cuser_link_bank_details}}');

        $this->dropColumn('{{%legal_person}}','default_bank_id');

        $this->dropColumn('{{%bills}}','bank_id');

        $this->dropColumn('{{%acts}}','bank_id');

        $this->dropColumn('{{%payment_request}}','bank_id');

        $this->dropColumn('{{%partner_w_bookkeeper_request}}','bank_id');

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
