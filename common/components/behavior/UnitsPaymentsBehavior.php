<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 17.08.15
 */

namespace common\components\behavior;


use app\models\Units;
use app\models\UnitsToManager;
use backend\models\BUser;
use common\components\helpers\CustomHelper;
use common\models\AbstractActiveRecord;
use common\models\CUser;
use common\models\Payments;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class UnitsPaymentsBehavior extends Behavior{

    protected
        $isNewRecord;

    /**
     * Назначаем событиям обработчики
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }


    /**
     * @return bool
     */
    public function afterInsert()
    {
        if(!$this->isNewRecord) //работаем только с новыми записями. обновление нас не интересует
            return TRUE;

        $iPayID = $this->owner->id;             // ID платежа
        $iCUserID = $this->owner->cuser_id;     // ID контрагента
        $sDate = $this->owner->pay_date;        // Дата платежа
        $iService = $this->owner->service_id;   // ID услуги

        /** @var Units $obUnit */
        $obUnit = Units::find()->where(['service_id' => $iService,'type' => Units::TYPE_PAYMENT])->one(); //ищем юнит
        if(empty($obUnit))
            return TRUE;

        /** @var BUser $obManager */
        $obManager = BUser::find()      //находим менеджера, для которого зачисляется unit
            ->select(['b.id','b.allow_unit'])
            ->alias('b')
            ->leftJoin(CUser::tableName().' as c','c.manager_id = b.id')
            ->where(['c.id' => $iCUserID])
            ->one();

        if(!$obManager || $obManager->allow_unit != AbstractActiveRecord::YES)  //проверяем нашли ли менеджера и разрешено ли менеджеру накапливать Units
            return TRUE;

        /*
        $iManager = CUser::find()->select('manager_id')->where(['id' => $iCUserID])->scalar();      //ID менеджера
        if(empty($iManager))
            return TRUE;
        */

        $cost = $obUnit->getCostForDate($sDate);    // получаем стоимость юнита на дату платежа

        if(is_null($cost))
            return FALSE;

        if($obUnit->multiple == Units::YES)     // зачислять за каждый платеж ?
        {
            return $this->saveManUnit($cost,$obManager->id,$iPayID,$obUnit->id,$sDate); // начисляем юнит
        }else{  //начислять только один раз в месяц за платеж
            $bManUnit = UnitsToManager::find()->where([   //проверяем не зачисляли ли в месяце на дату платежа.
                    'manager_id' => $obManager->id,
                    'unit_id' => $obUnit->id
                ])
                ->andWhere(' (( updated_at >= '.CustomHelper::getBeginMonthTime($sDate).' AND '.
                ' updated_at <= '.CustomHelper::getEndMonthTime($sDate).' ) OR ( pay_date >= '.
                    CustomHelper::getBeginMonthTime($sDate).' AND pay_date <= '.
                    CustomHelper::getEndMonthTime($sDate).' )) ')
                ->one();

            if(!empty($bManUnit)) //если было зачисление
                return TRUE;

            return $this->saveManUnit($cost,$obManager->id,$iPayID,$obUnit->id,$sDate); //начисляем юнит
        }
        return FALSE;
    }

    /**
     * Добавление нового юнита.
     * @param $cost
     * @param $iManager
     * @param $iPayID
     * @param $iUnitID
     * @param $sDate
     * @return bool
     */
    protected function saveManUnit($cost,$iManager,$iPayID,$iUnitID,$sDate)
    {
        /** @var UnitsToManager $obUMan */
        $obUMan = new UnitsToManager();
        $obUMan->cost = $cost;
        $obUMan->manager_id = $iManager;
        $obUMan->payment_id = $iPayID;
        $obUMan->unit_id = $iUnitID;
        $obUMan->pay_date = $sDate;
        return $obUMan->save();
    }

    /**
     * Удалили платеж, удалим юниты.
     * @return bool
     */
    public function afterDelete()
    {
        $iPayID = $this->owner->id;             // ID платежа
        $iCUserID = $this->owner->cuser_id;     // ID контрагента
        $sDate = $this->owner->pay_date;        // Дата платежа
        $iService = $this->owner->service_id;   // ID услуги

        /** @var Units $obUnit */
        $obUnit = Units::find()->where(['service_id' => $iService,'type' => Units::TYPE_PAYMENT])->one(); //ищем юнит
        if(empty($obUnit) || $obUnit->multiple == Units::YES)  //если не нашли, удалим юниты по платежу или если юниты зачисляются несколбко раз
        {
            UnitsToManager::deleteAll(['payment_id' => $iPayID]);
            return TRUE;
        }

        UnitsToManager::deleteAll(['payment_id' => $iPayID]);

        $obPayment = Payments::find()
            ->where('pay_date >= :beginDate && pay_date <= :endDate')
            ->andWhere(['!=','cuser_id',$iCUserID])
            ->params([
                ':beginDate' => CustomHelper::getBeginMonthTime($sDate),
                ':endDate' => CustomHelper::getEndMonthTime($sDate)
            ])
            ->orderBy(['pay_date' => SORT_ASC])
            ->one();
        if(!$obPayment)
            return TRUE;



        UnitsToManager::deleteAll(['payment_id' => $iPayID]);
        return TRUE;
    }

    /**
     *
     */
    public function beforeInsert()
    {
        $this->isNewRecord = $this->owner->isNewRecord;
    }

} 