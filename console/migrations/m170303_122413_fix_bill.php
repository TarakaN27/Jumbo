<?php

use yii\db\Migration;
use common\models\Bills;
class m170303_122413_fix_bill extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bills}}','bank_id', $this->integer());
        $bills = Bills::find()->all();
        foreach($bills as $bill){
            if($bill->bill_date>="2017-03-01" ){
                $link = \common\models\CuserBankDetails::findOne(['legal_person_id'=>$bill->l_person_id, 'cuser_id'=>$bill->cuser_id]);
                if($link && $link->bank_details_id){
                    $bill->bank_id = $link->bank_details_id;
                }else{
                    $bill->bank_id = $bill->lPerson->default_bank_id;
                }
            }
            else{
                $bill->bank_id = $bill->lPerson->default_bank_id;
            }
            $bill->save();
        }

    }
    public function down()
    {
        $this->dropColumn('{{%bills}}','bank_id', $this->integer());
        return true;
    }

}
