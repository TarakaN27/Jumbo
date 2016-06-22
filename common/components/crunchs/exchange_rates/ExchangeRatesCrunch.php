<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 8.6.16
 * Time: 11.00
 */

namespace common\components\crunchs\exchange_rates;

use common\components\ExchangeRates\ExchangeRatesCBRF;
use common\components\ExchangeRates\ExchangeRatesNBRB;
use common\models\ExchangeRates;
use common\models\ExchangeCurrencyHistory;
use Faker\Provider\DateTime;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;


class ExchangeRatesCrunch
{
    protected
        $_error = [];

    /**
     * @param $str
     */
    protected function addError($str)
    {
        $this->_error [] = $str;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_error;
    }

    public function RecoveryExchangeRates($beginDate,$endDate)
    {
        $beginDate = '2016-06-10';                  //дата с которой начинаем восстановление
        $endDate = '2016-06-15';                    //дата на которой заканчиваем восстановление
        $arRowsForInsert = [];                      //массив с строками для вставки в базу данных

        /** @var array $arCurrency */
        $arCurrency = ExchangeRates::find()->where(['need_upd' => ExchangeRates::YES])->all();  //массив с валютами для обновления
        if (empty($arCurrency))
        {
            $this->addError('Exchanges rates not found');
            return false;
        }
        
        $arCurrencyHistoryTmp = ExchangeCurrencyHistory::find()//история обновления валют
        ->where(['currency_id' => ArrayHelper::getColumn($arCurrency, 'id')])
            ->andWhere(['between', 'date', $beginDate, $endDate])
            ->orderBy(['date' => SORT_ASC])
            ->all();

        $arCurrHist = [];
        
        $arCurrHist = ArrayHelper::index($arCurrencyHistoryTmp,'date',['currency_id']);         //групируем по дате и делаем ключамии id валюты

        /** @var ExchangeRates $obCurr */
        foreach ($arCurrency as $obCurr)
        {
            sleep(5);
            if($obCurr->use_base || $obCurr->use_exchanger)
                continue;
            if($obCurr->nbrb != 0) {
                $obNBRB = new ExchangeRatesNBRB();
                $arRBNB = $obNBRB->getCurrencyRateByPeriod($obCurr->nbrb, strtotime($beginDate), strtotime($endDate));
                if (!$arRBNB)
                    throw new ServerErrorHttpException();
            }else{
                $arRBNB  = [];
            }

            $obDateBegin = new \DateTime($beginDate);
            $obDateEnd = new \DateTime($endDate);
            $arLast = NULL;
            while ($obDateBegin < $obDateEnd)
            {
                $dateTmp = $obDateBegin->getTimestamp();
                if(!isset($arCurrHist[$obCurr->id]) || (isset($arCurrHist[$obCurr->id]) && !isset($arCurrHist[$obCurr->id][ $obDateBegin->format('Y-m-d')])))
                {

                    if($obCurr->cbr != 0)
                    {
                        $obCBRF = new ExchangeRatesCBRF($obCurr->cbr,$dateTmp);
                        $fCurrCBRF = $obCBRF->makeRequest();
                    }else{
                        $fCurrCBRF = 1;
                    }

                    if($obCurr->use_rur_for_byr)
                    {
                        $crb = new ExchangeRatesCBRF(ExchangeRatesCBRF::BUR_IN_CBR_CODE,$dateTmp);
                        $curr = $crb->getRURcurrencyInBur();

                        $fCurrNBRB = round($fCurrCBRF*$curr,4); //курс по ЦБРФ
                    }else{
                        if($obCurr->nbrb != 0)
                        {
                            $fCurrNBRB = $arRBNB[$obDateBegin->format('Y-m-d')];
                            //$obNBRB = new ExchangeRatesNBRB($obCurr->nbrb,$dateTmp);
                            //$fCurrNBRB = $obNBRB->makeRequest();
                            //unset($obNBRB);
                        }else{
                            $fCurrNBRB = 1;
                        }
                    }

                    $arRowsForInsert [] = [
                        '',
                        $obCurr->id,
                        $fCurrNBRB,
                        $arLast ? $arLast['rate_nbrb'] : 0,
                        $fCurrCBRF,
                        $arLast ? $arLast['rate_cbr'] : 0,
                        '',
                        $obDateBegin->format('Y-m-d'),
                        time(),
                        time()
                    ];

                    $arLast = [
                        'rate_nbrb' => $fCurrNBRB,
                        'rate_cbr' => $fCurrCBRF
                    ];
                    
                }else{
                    $obLast = $arCurrHist[$obCurr->id][ $obDateBegin->format('Y-m-d')];
                    $arLast = [
                        'rate_nbrb' => $obLast->rate_nbrb,
                        'rate_cbr' => $obLast->rate_cbr
                    ];
                }
                $obDateBegin->modify('+1 day');
            }

            unset($obDateBegin,$obDateEnd);
        }

        if($arRowsForInsert) {
            $postModel = new ExchangeCurrencyHistory();
            //групповое добавление
            \Yii::$app->db->createCommand()
                ->batchInsert(ExchangeCurrencyHistory::tableName(), $postModel->attributes(), $arRowsForInsert)
                ->execute();
        }

        $arRowsForInsert  = [];
        /** @var array $arCurrency */
        $arCurrency = ExchangeRates::find()->where(['need_upd' => ExchangeRates::YES])->all();  //массив с валютами для обновления
        if (empty($arCurrency))
        {
            $this->addError('Exchanges rates not found');
            return false;
        }

        $arCurrencyHistoryTmp = ExchangeCurrencyHistory::find()//история обновления валют
        ->where(['currency_id' => ArrayHelper::getColumn($arCurrency, 'id')])
            ->andWhere(['between', 'date', $beginDate, $endDate])
            ->orderBy(['date' => SORT_ASC])
            ->all();

        $arCurrHist = ArrayHelper::index($arCurrencyHistoryTmp,'date',['currency_id']);         //групируем по дате и делаем ключамии id валюты

        foreach ($arCurrency as $obCurr)
        {
            if(!$obCurr->use_base)
                continue;

            $obDateBegin = new \DateTime($beginDate);
            $obDateEnd = new \DateTime($endDate);
            $arLast = NULL;
            while ($obDateBegin < $obDateEnd)
            {
                $dateTmp = $obDateBegin->getTimestamp();
                if(!isset($arCurrHist[$obCurr->id]) || (isset($arCurrHist[$obCurr->id]) && !isset($arCurrHist[$obCurr->id][ $obDateBegin->format('Y-m-d')])))
                {
                    if(isset($arCurrHist[$obCurr->base_id],$arCurrHist[$obCurr->base_id][ $obDateBegin->format('Y-m-d')]))
                    {
                        /** @var ExchangeCurrencyHistory $tmpI */
                        $tmpI = $arCurrHist[$obCurr->base_id][ $obDateBegin->format('Y-m-d')];

                        $arRowsForInsert [] = [
                            '',
                            $obCurr->id,
                            round($tmpI->rate_nbrb*$obCurr->factor,6),
                            $arLast ? $arLast['rate_nbrb'] : 0,
                            round($tmpI->rate_cbr*$obCurr->factor,6),
                            $arLast ? $arLast['rate_cbr'] : 0,
                            '',
                            $obDateBegin->format('Y-m-d'),
                            time(),
                            time()
                        ];

                        $arLast = [
                            'rate_nbrb' => round($tmpI->rate_nbrb*$obCurr->factor,6),
                            'rate_cbr' => round($tmpI->rate_cbr*$obCurr->factor,6)
                        ];
                    }
                }else{
                    /** @var ExchangeCurrencyHistory $obLast */
                    $obLast = $arCurrHist[$obCurr->id][$obDateBegin->format('Y-m-d')];
                    $arLast = [
                        'rate_nbrb' => $obLast->rate_nbrb,
                        'rate_cbr' => $obLast->rate_cbr
                    ];
                }
                $obDateBegin->modify('+1 day');
            }
        }

        //групповое добавление
        if($arRowsForInsert) {
            $postModel = new ExchangeCurrencyHistory();
            \Yii::$app->db->createCommand()
                ->batchInsert(ExchangeCurrencyHistory::tableName(), $postModel->attributes(), $arRowsForInsert)
                ->execute();
        }

        /*
        foreach ($arCurrencyHistoryTmp as $currTmp) {
            $arCurrHist[$currTmp->date][] = $currTmp;
        }
        
        foreach ($arCurrHist as &$hist)
        {
            $hist = ArrayHelper::index($hist,'currency_id');
        }
        */

        return true;
    }


}