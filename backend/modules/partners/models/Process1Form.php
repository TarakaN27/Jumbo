<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 28.4.16
 * Time: 10.36
 */

namespace backend\modules\partners\models;


use common\models\AbstractActiveRecord;
use common\models\LegalPerson;
use common\models\PartnerWBookkeeperRequest;
use common\models\PartnerWithdrawalRequest;
use yii\base\Model;
use Yii;

class Process1Form extends Model
{
    public
        $obBookkeeper,
        $obRequest,
        $amount,
        $currency,
        $legalPerson,
        $contractor,
        $description;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['amount','legalPerson'],'required'],
            ['amount','number'],
            [['currency','legalPerson','contractor'],'integer'],
            ['description','string','max' => 255],
            ['contractor','customValidate','skipOnEmpty' => false, 'skipOnError' => false],
            ['obRequest','safe']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'amount' => Yii::t('app/users','Amount'),
            'legalPerson' => Yii::t('app/users','Legal person'),
            'contractor' => Yii::t('app/users','Contractor')
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function customValidate($attribute,$params)
    {
        if( empty($this->contractor) &&
            LegalPerson::find()->where(['id' => $this->legalPerson,'partner_cntr' => AbstractActiveRecord::YES])->exists())
        {
            $this->addError($attribute,\Yii::t('app/users','Is required'));
        }
    }

    /**
     * @return bool
     */
    public function makeRequest()
    {
        if(!$this->obBookkeeper)
            return FALSE;

        if(!$this->obRequest)
            return FALSE;

        $obBRequest = new PartnerWBookkeeperRequest();
        $obBRequest->partner_id = $this->obRequest->partner_id;
        $obBRequest->buser_id = $this->obBookkeeper->id;
        $obBRequest->contractor_id = $this->contractor;
        $obBRequest->amount = $this->amount;
        $obBRequest->currency_id = $this->obRequest->currency_id;
        $obBRequest->legal_id = $this->legalPerson;
        $obBRequest->request_id = $this->obRequest->id;
        $obBRequest->created_by = Yii::$app->user->id;
        $obBRequest->status = PartnerWBookkeeperRequest::STATUS_NEW;
        return $obBRequest->save();
    }

}