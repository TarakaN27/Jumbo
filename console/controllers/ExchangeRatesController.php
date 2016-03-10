<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace console\controllers;

use common\components\ExchangeRates\ExchangeRatesCBRF;
use common\components\ExchangeRates\ExchangeRatesNBRB;
use common\components\ExchangeRates\ExchangeRatesObmennikBY;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use console\components\AbstractConsoleController;
use yii\web\NotFoundHttpException;

class ExchangeRatesController extends AbstractConsoleController{

    /**
     * @return int
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionRun()
    {
        /** @var ExchangeRates $arCurrency */
        $arCurrency = ExchangeRates::find()->where(['need_upd' => ExchangeRates::YES])->all();
        if(empty($arCurrency))
            return $this->log(TRUE);

        $obNBRB = new ExchangeRatesNBRB();
        $arCurrNBRB = $obNBRB->getAllCurrency();

        $obCBRF = new ExchangeRatesCBRF();
        $arCurrCBRF = $obCBRF->getAllCurrency();
        unset($obCBRF,$obNBRB);

        $bHasError = FALSE;
        /**
         * вначале обновляем курсы валют , которые получаем из центр. банков
         */
        $trans = \Yii::$app->db->beginTransaction();
        try{
            /** @var  ExchangeRates $model*/
            foreach($arCurrency as $key => $model)
            {
                if($model->use_base || $model->use_exchanger)
                    continue;

                $nbrbRate = $model->nbrb_rate;
                $crbRate = $model->cbr_rate;

                if($model->cbr != 0)
                {
                    if(isset($arCurrCBRF[$model->cbr]))
                        $crbRate = $arCurrCBRF[$model->cbr];
                }else{
                    $crbRate = 1;
                }

                if($model->use_rur_for_byr)
                {
                    $crb = new ExchangeRatesCBRF(ExchangeRatesCBRF::BUR_IN_CBR_CODE);
                    $curr = $crb->getRURcurrencyInBur();

                    $nbrbRate = round($crbRate*$curr,4); //курс по ЦБРФ
                }else{
                    if($model->nbrb != 0)
                    {
                        if(isset($arCurrNBRB[$model->nbrb]))
                            $nbrbRate = $arCurrNBRB[$model->nbrb];
                    }else{
                        $nbrbRate = 1;
                    }
                }

                if((!empty($nbrbRate) || $model->nbrb == 0) && (!empty($crbRate) || $model->cbr == 0))
                {
                    $model->cbr_rate = $crbRate;
                    $model->nbrb_rate= $nbrbRate;
                    if(!$model->save())
                    {
                        $bHasError = TRUE;
                    }
                }else{
                    $bHasError = TRUE;
                }

                unset($arCurrency[$key]);
            }
            /** @var  ExchangeRates $item*/
            foreach($arCurrency as $item)
            {
                if($item->use_base) //так как основные валюты уже обновили, обновим зависимые валюты
                {
                    /** @var ExchangeRates $obBase */
                    $obBase = ExchangeRates::findOne(['id' => $item->base_id]);
                    if(empty($obBase))
                        throw new NotFoundHttpException('Base currency not found');

                    $item->cbr_rate = round($obBase->cbr_rate*$item->factor,4);
                    $item->nbrb_rate = round($obBase->nbrb_rate*$item->factor,4);

                    if(!$item->save())
                    {
                        $bHasError = TRUE;
                    }
                }elseif($item->use_exchanger) //обновим фиксированные валюты
                {
                    $obExch = new ExchangeRatesObmennikBY();
                    $obExch ->setBankID($item->bank_id);

                    $data = $obExch ->getCurrencyUSD();

                    if(empty($data))
                        throw new NotFoundHttpException('Cant get currency from obmennik.by');

                    $factor = empty($item->factor) ? 1 : $item->factor;

                    $item->cbr_rate = $data['rur']*$factor;
                    $item->nbrb_rate = $data['bur']*$factor;

                    if(!$item->save())
                    {
                        $bHasError = TRUE;
                    }
                }
            }

            if($bHasError)
                $trans->rollBack();
            else
                $trans->commit();

        }catch (\Exception $e){
            $trans->rollBack();
            $bHasError = TRUE;
        }

        return $this->log(!$bHasError);
    }

    /**
     * @return int
     */
    public function actionRecalculate()
    {
        $curIDs = [4,5,6,9];
        $fromDate = '2015-12-25';
        $toDate = '2016-02-22';

        $exHist = ExchangeCurrencyHistory::find()
            ->where(['currency_id' => $curIDs])
            ->andWhere('date > :fromDate AND date < :toDate')
            ->params([':fromDate' => $fromDate,':toDate' => $toDate])
            ->all();

        $arByDate = [];
        foreach($exHist as $hist)
            $arByDate[$hist->date][] = $hist;

        foreach($arByDate as $date => $arHist)
        {
            $obCBRF = new ExchangeRatesCBRF(NULL,strtotime($date));
            $curr = $obCBRF->getRURcurrencyInBur();
            echo $date.'-'.$curr.PHP_EOL;
            /** @var ExchangeCurrencyHistory $ex */
            foreach($arHist as $ex)
            {
                $new = round($curr*$ex->rate_cbr,4);
                $old = round($curr*$ex->old_rate_cbr,4);
                $ex->rate_nbrb = $new;
                $ex->old_rate_nbrb = $old;
                $ex->save();
            }
        }



        return self::EXIT_CODE_NORMAL;
    }
}