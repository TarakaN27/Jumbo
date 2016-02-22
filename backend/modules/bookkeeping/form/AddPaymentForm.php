<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 23.07.15
 */

namespace backend\modules\bookkeeping\form;


use common\models\AbstractModel;
use common\models\PaymentCondition;
use Yii;

class AddPaymentForm extends AbstractModel{

    public
        $condType,
        $customProduction,
        $showAll,
        $fullSumm,
        $comment,
        $service,
        $condID,
        $summ;

    public function rules()
    {
        return [
            [['summ','service','condID'],'required'],
            [['service','condID','condType'], 'integer'],
            [['summ','fullSumm','customProduction'], 'number'],
            [['comment'], 'string'],

            ['condType','required','when' => function($model) {
                if($this->condType == PaymentCondition::TYPE_CUSTOM) //если компания не контрагнет, то поля можно не заполнять
                    return FALSE;
                return TRUE;
            }]
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'showAll' => Yii::t('app/book','Show all conditions'),
            'summ' => Yii::t('app/book', 'Summ'),
            'service' => Yii::t('app/book', 'Service'),
            'condID' => Yii::t('app/book', 'Condition'),
            'comment' => Yii::t('app/book', 'Comment'),
            'customProduction' => Yii::t('app/book','Custom amount production'),
            'condType' => Yii::t('app/book','Condition type')
        ];
    }







} 