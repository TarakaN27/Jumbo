<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 23.07.15
 */

namespace backend\modules\bookkeeping\form;


use common\components\customComponents\validation\ValidNumber;
use common\components\helpers\CustomHelper;
use common\models\AbstractModel;
use common\models\PaymentCondition;
use Yii;

class AddPaymentForm extends AbstractModel{

    public
        $isSale,
        $saleUser,
        $condType,
        $customProduction,
        $showAll,
        $fullSumm,
        $comment,
        $curr_val,
        $service,
        $condID,
        $hide_act_payment,
        $post_payment,
        $summ;

    public function rules()
    {
        return [
            [['summ','condID'],'required'],
            [['summ'],ValidNumber::className()],
            [['service','condID','condType','isSale','saleUser','hide_act_payment', 'post_payment', 'showAll'], 'integer'],
            [['summ','fullSumm','customProduction', 'curr_val'], 'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['comment'], 'string'],
            ['condType','required','when' => function($model) {
                if($this->condType == PaymentCondition::TYPE_CUSTOM) //если компания не контрагнет, то поля можно не заполнять
                    return FALSE;
                return TRUE;
            }],
            ['service','required','when' => function($model) {
                if($model->summ > 0) //если компания не контрагнет, то поля можно не заполнять
                    return TRUE;
                else
                    return false;
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
            'condType' => Yii::t('app/book','Condition type'),
            'isSale' => Yii::t('app/book','Is sale'),
            'saleUser' => Yii::t('app/book','Sale user'),
            'hide_act_payment' => Yii::t('app/book','Hide payment at act'),
            'post_payment' => Yii::t('app/book','Post payment'),
            'curr_val' => Yii::t('app/book','Currency value'),
        ];
    }







} 