<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 24.07.15
 */

namespace backend\modules\bookkeeping\form;


use common\models\AbstractModel;
use common\models\PaymentRequest;

class SetManagerContractorForm extends AbstractModel{

    public
        $contractor,
        $obPR,
        $contractorMap;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['contractor'] ,'required'],
            ['contractor','in', 'range' => array_keys($this->contractorMap)],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return[
            'contractor' => \Yii::t('app/book','Contractor')
        ];
    }

    /**
     * @return mixed
     */
    public function makeRequest()
    {
        $this->obPR->cntr_id = $this->contractor;
        $this->obPR->is_unknown = PaymentRequest::NO;
        $this->obPR->manager_id = \Yii::$app->user->id;

        return $this->obPR->save();
    }
} 