<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 4:26 PM
 */

namespace backend\modules\bookkeeping\form;


use yii\base\Model;

class EnrollProcessForm extends Model{

    public
        $isPayment,
        $request,   //сам запрос
        $enroll,    //зачислено
        $repay;     //погашено


    public function rules()
    {
        return [
            ['isPayment','integer'],
            [['enroll','repay'],'number']
        ];
    }

    public function makeRequest()
    {

    }
} 