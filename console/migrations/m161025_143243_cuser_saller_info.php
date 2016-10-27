<?php

use yii\db\Migration;
use common\models\Payments;
use common\models\PaymentsSale;
use common\models\CUser;
use yii\db\Schema;

class m161025_143243_cuser_saller_info extends Migration
{
    public function up()
    {

        $payments = Payments::find()->orderBy(['pay_date'=>SORT_ASC])->all();
        $group = \common\models\CuserToGroup::find()->all();
        $tempGroup = [];
        foreach($group as $item){
            if(!isset($tempGroup[$item->group_id]))
                $tempGroup[$item->group_id]=[];
            $tempGroup[$item->group_id][] = $item->cuser_id;
        }
        $group = \yii\helpers\ArrayHelper::map($group,'cuser_id', 'group_id');
        $temp = [];
        foreach($payments as $item){
            if(array_key_exists($item->cuser_id,$group)){
                $keyItem = 'group_'.$group[$item->cuser_id];
            }else
                $keyItem = $item->cuser_id;
            if(isset($temp[$keyItem])) {
                $key = count($temp[$keyItem])-1;
                if(($item->pay_date - $temp[$keyItem][$key])>60*60*24*30*4){
                    $temp[$keyItem]=[];
                }
                $temp[$keyItem][] = $item->pay_date;
            }else{
                $temp[$keyItem][] = $item->pay_date;
            }
        }
        $paymentSale= PaymentsSale::find()->orderBy(['sale_date'=>SORT_DESC])->all();
        $tempSales = [];
        foreach($paymentSale as $key=>$item){
            $tempSales[$item->cuser_id] = $item->buser_id;
        }

        $this->addColumn('{{%c_user}}','sale_manager_id', $this->integer());
        $this->addColumn('{{%c_user}}','sale_date', $this->integer());

        foreach($temp as $key=>$item){
            $cuserId = $key;
            $saleManagerId = null;
            if(strpos($key, 'group')!==false){
                $groupId = str_replace("group_","",$key);
                $cuserId = $tempGroup[$groupId];
                foreach($cuserId as $cuserTemp){
                    if(isset($tempSales[$cuserTemp])){
                        $saleManagerId = $tempSales[$cuserTemp];
                        break;
                    }
                }
            }else{
                if(isset($tempSales[$key]))
                    $saleManagerId = $tempSales[$key];
            }
            CUser::updateAll(['sale_manager_id'=>$saleManagerId, 'sale_date'=>$item[0]],['id'=>$cuserId]);
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%b_user_bonus_month_coeff}}',[
            'id' => Schema::TYPE_PK,
            'buser_id' => Schema::TYPE_INTEGER.' NOT NULL',
            'month' => Schema::TYPE_INTEGER.' NOT NULL',
            'year' => Schema::TYPE_INTEGER.' NOT NULL',
            'coeff' => $this->decimal(10,5),
        ],$tableOptions);
    }

    public function down()
    {
        $this->dropColumn('{{%c_user}}','sale_manager_id');
        $this->dropColumn('{{%c_user}}','sale_date');
        $this->dropTable('{{%b_user_bonus_month_coeff}}');
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
