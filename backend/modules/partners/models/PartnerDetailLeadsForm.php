<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 21.4.16
 * Time: 12.56
 */

namespace backend\modules\partners\models;


use common\models\PartnerCuserServ;
use common\models\PartnerPurseHistory;
use yii\base\Model;
use Yii;
use yii\helpers\ArrayHelper;

class PartnerDetailLeadsForm extends Model
{
    public 
        $beginDate = NULL,
        $endDate = NULL,
        $obPartner = NULL;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['obPartner','safe'],
            [['beginDate','endDate'],'required'],
            [['beginDate','endDate'],'date','format' => 'php:d.m.Y'],
            [['beginDate','endDate'],'validateDate']
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateDate($attribute,$params)
    {
        if(strtotime($this->beginDate) > strtotime($this->endDate))
            $this->addError($attribute,\Yii::t('app/users','Begin date must be less than end date'));
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'beginDate' => Yii::t('app/users','Begin date'),
            'endDate' => Yii::t('app/users','End date')
        ];
    }


    public function makeRequest()
    {
        $beginTime = strtotime($this->beginDate.' 00:00:00');
        $endTime = strtotime($this->endDate.' 00:00:00');
        $arResult = [];
        $arResult['prev'] = $this->getPervPeriodStat($beginTime);   //получаем сумму
        $arLeads =  $this->getLeads();
        $arCurrPeriod = $this->getCurrentPeriodStat($beginTime,$endTime);










        
        
        return [];
    }


    /**
     * @return array
     */
    protected function getPervPeriodStat($beginDate)
    {
        $sumIncoming = PartnerPurseHistory::find()
            ->select(['h.id','p.id as pid','p.pay_date','h.type','h.amount','h,payment_id'])
            ->alias('h')
            ->joinWith('payment p')
            ->where('p.pay_date < :beginDate')
            ->andWhere(['h.type' => PartnerPurseHistory::TYPE_INCOMING])
            ->params([':beginDate' => $beginDate])
            ->sum('h.amount');

        $sumExpense = PartnerPurseHistory::find()
            ->select(['h.id','p.id as pid','p.pay_date','h.type','h.amount','h,payment_id'])
            ->alias('h')
            ->joinWith('payment p')
            ->where('p.pay_date < :beginDate')
            ->andWhere(['h.type' => PartnerPurseHistory::TYPE_EXPENSE])
            ->params([':beginDate' => $beginDate])
            ->sum('h.amount');

        return [
            'incoming' => (float)$sumIncoming,
            'expense' => (float)$sumExpense
        ];
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @return mixed
     */
    protected function getCurrentPeriodStat($beginDate,$endDate)
    {
        return PartnerPurseHistory::find()
            ->select(['h.*','p.id as pid','p.pay_summ','p.cuser_id as pcuser_id','p.currency_id','p.pay_date'])
            ->alias('h')
            ->joinWith('payment p')
            ->joinWith('service s')
            ->where('p.pay_date <= :beinDate AND p.pay_date >= :endDate ')
            ->params([':beginDate' => $beginDate,':endDate' => $endDate])
            ->sum('h.amount');
    }

    /**
     * @return mixed
     */
    protected function getLeads()
    {
        return PartnerCuserServ::find()->where(['partner_id' => $this->obPartner->id])->all();
    }


}