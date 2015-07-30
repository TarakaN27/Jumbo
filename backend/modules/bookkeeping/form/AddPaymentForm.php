<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 23.07.15
 */

namespace backend\modules\bookkeeping\form;


use common\models\AbstractModel;
use Yii;

class AddPaymentForm extends AbstractModel{

    public
        $fullSumm,
        $comment,
        $service,
        $condID,
        $summ;

    public function rules()
    {
        return [
            [['summ','service','condID'],'required'],
            [['service','condID'], 'integer'],
            [['summ','fullSumm'], 'number'],
            [['comment'], 'string']
        ];
    }








} 