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
        $summ;

    public function rules()
    {
        return [
            [['summ','service'],'required'],
            [['service'], 'integer'],
            [['summ','fullSumm'], 'number'],
            [['comment'], 'string']
        ];
    }








} 