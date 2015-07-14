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
use common\models\ExchangeRates;
use console\components\AbstractConsoleController;

class ExchangeRatesController extends AbstractConsoleController{
    /**
     * Update currency exchange rates
     * @return int
     */
    public function actionRun()
    {
        $arCurrency = ExchangeRates::find()->where(['need_upd' => ExchangeRates::YES])->all();
        if(empty($arCurrency))
            return $this->log(TRUE);

        $obNBRB = new ExchangeRatesNBRB();
        $arCurrNBRB = $obNBRB->getAllCurrency();

        $obCBRF = new ExchangeRatesCBRF();
        $arCurrCBRF = $obCBRF->getAllCurrency();
        unset($obCBRF,$obNBRB);

        $bHasError = FALSE;
        
        foreach($arCurrency as $model)
        {
            if($model->nbrb != 0)
            {
                if(isset($arCurrNBRB[$model->nbrb]))
                    $nbrbRate = $arCurrNBRB[$model->nbrb];
            }else{
                $nbrbRate = $model->nbrb_rate;
            }
            if($model->cbr != 0)
            {
                if(isset($arCurrCBRF[$model->cbr]))
                    $crbRate = $arCurrCBRF[$model->cbr];
            }else{
                $crbRate = $model->cbr_rate;
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
        }

        return $this->log(!$bHasError);
    }
}