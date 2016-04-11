<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.4.16
 * Time: 12.05
 */

namespace common\components\crunchs\Payment;


use common\models\Payments;
use common\models\CuserQuantityHour;
use common\models\ServiceRateHist;
use common\models\ExchangeCurrencyHistory;
use yii\base\Exception;
use yii\web\ServerErrorHttpException;
use common\models\CrmTaskLogTime;
use common\models\CrmTask;

class RecalcQuantityHours
{
    public function run()
    {
        $tr  = \Yii::$app->db->beginTransaction();
        try {
            CuserQuantityHour::deleteAll();
            $payments = Payments::find()->select(['cuser_id', 'pay_summ','pay_date', 'currency_id', 'id', 'service_id'])->all();
            foreach ($payments as $pay) {
                $this->countPaidHours($pay);
            }

            $crmLogTime = CrmTaskLogTime::find()->all();
            foreach ($crmLogTime as $log)
                $this->countingSpentTime($log);
            $tr->commit();
            echo 'done';
            return TRUE;
        }catch (Exception $e)
        {
            $tr->rollBack();
            var_dump($e);
            return FALSE;
        }
    }

    /**
     * @param Payments $model
     * @throws ServerErrorHttpException
     */
    protected function countPaidHours(Payments $model)
    {
        $obQHour = CuserQuantityHour::find()->where(['cuser_id' => $model->cuser_id])->one();   //находим нормачасы
        if(!$obQHour)   //не заведены, добавим
        {
            $obQHour = new CuserQuantityHour();
            $obQHour->spent_time = 0;
            $obQHour->cuser_id = $model->cuser_id;
        }

        $date = $this->getFormatedDate($model);   //форматирвоанная дата
        $rate = ServiceRateHist::getRateForDate($model->service_id,$date);  //Получаем ставку норма часа на дату платежа

        if($rate > 0 )  //есть ставка, продолжим
        {
            $currBUR = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$model->currency_id); //курс валюта на заданное число
            $amount = $model->pay_summ*$currBUR;
            $hours = round($amount/$rate,2);    //вычисляем кол-во часов
            $obQHour->hours+=$hours;
            if(!$obQHour->save())
            {
                throw new ServerErrorHttpException('Can not save the required quantity of hours');
            }
        }
    }

    /**
     * @param CrmTaskLogTime $model
     * @return bool
     * @throws ServerErrorHttpException
     */
    protected function countingSpentTime(CrmTaskLogTime $model)
    {
        $obTask = CrmTask::find()->select(['cmp_id'])->where(['id' => $model->task_id])->one();
        if(empty($obTask))
            return FALSE;

        $obQHour = CuserQuantityHour::find()->where(['cuser_id' => $obTask->cmp_id])->one();
        if(!$obQHour)
        {
            $obQHour = new CuserQuantityHour();
            $obQHour->cuser_id = $obTask->cmp_id;
            $obQHour->hours = 0;
        }

        $hours = round($model->spend_time/3600,2);
        $obQHour->spent_time+=$hours;
        if(!$obQHour->save())
            throw new ServerErrorHttpException('Can not save quantity hour');
    }

    /**
     * @return bool|string
     */
    protected function getFormatedDate($model)
    {
        return date('Y-m-d',$model->pay_date);
    }


}