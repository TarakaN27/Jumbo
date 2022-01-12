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
use common\components\helpers\CustomDateHelper;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use console\components\AbstractConsoleController;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class ExchangeRatesController extends AbstractConsoleController{

    /**
     * @return int
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionRun($failCount = 5)
    {
        $failCount--;
        /** @var ExchangeRates $arCurrency */
        $arCurrency = ExchangeRates::find()->where(['need_upd' => ExchangeRates::YES])->all();
        if(empty($arCurrency))
            return $this->log(TRUE);
        do {
            $trans = \Yii::$app->db->beginTransaction();
            try {
                $obNBRB = new ExchangeRatesNBRB();
                $arCurrNBRB = $obNBRB->getAllCurrency();

                $obCBRF = new ExchangeRatesCBRF();
                $arCurrCBRF = $obCBRF->getAllCurrency();
                unset($obCBRF, $obNBRB);

                $bHasError = FALSE;
                /**
                 * вначале обновляем курсы валют , которые получаем из центр. банков
                 */

                /** @var  ExchangeRates $model */
                foreach ($arCurrency as $key => $model) {
                    if ($model->fix_exchange || $model->use_base || $model->use_exchanger)
                        continue;

                    $nbrbRate = $model->nbrb_rate;
                    $crbRate = $model->cbr_rate;

                    if ($model->cbr != 0) {
                        if (isset($arCurrCBRF[$model->cbr]))
                            $crbRate = $arCurrCBRF[$model->cbr];
                    } else {
                        $crbRate = 1;
                    }

                    if ($model->use_rur_for_byr) {
                        $code = CustomDateHelper::isDateBeforeOrAfterDate('01-07-2016') ? ExchangeRatesCBRF::BYN_IN_CBR_CODE : ExchangeRatesCBRF::BYR_IN_CBR_CODE;

                        $crb = new ExchangeRatesCBRF($code);
                        $curr = $crb->getRURcurrencyInBur();

                        $nbrbRate = round($crbRate * $curr, 4); //курс по ЦБРФ
                    } else {
                        if ($model->nbrb != 0) {
                            if (isset($arCurrNBRB[$model->nbrb]))
                                $nbrbRate = $arCurrNBRB[$model->nbrb];
                        } else {
                            $nbrbRate = 1;
                        }
                    }

                    if ((!empty($nbrbRate) || $model->nbrb == 0) && (!empty($crbRate) || $model->cbr == 0)) {
                        $model->cbr_rate = $crbRate;
                        $model->nbrb_rate = $nbrbRate;
                        if (!$model->save()) {
                            $bHasError = TRUE;
                        }
                    } else {
                        $bHasError = TRUE;
                    }

                    unset($arCurrency[$key]);
                }
                /** @var  ExchangeRates $item */
                foreach ($arCurrency as $item) {
                    if ($item->use_base) //так как основные валюты уже обновили, обновим зависимые валюты
                    {
                        /** @var ExchangeRates $obBase */
                        $obBase = ExchangeRates::findOne(['id' => $item->base_id]);
                        if (empty($obBase))
                            throw new NotFoundHttpException('Base currency not found');

                        $item->cbr_rate = round($obBase->cbr_rate * $item->factor, 4);
                        $item->nbrb_rate = round($obBase->nbrb_rate * $item->factor, 4);

                        if (!$item->save()) {
                            $bHasError = TRUE;
                        }
                    } elseif ($item->use_exchanger) //обновим фиксированные валюты
                    {
                        $obExch = new ExchangeRatesObmennikBY();
                        $obExch->setBankID($item->bank_id);

                        $data = $obExch->getCurrencyUSD();

                        if (empty($data)) {
                            throw new NotFoundHttpException('Cant get currency from obmennik.by ' . $item->name);
                        }

                        $factor = empty($item->factor) ? 1 : $item->factor;

                        $item->cbr_rate = $data['rur'] * $factor;
                        $item->nbrb_rate = $data['bur'] * $factor;

                        if (!$item->save()) {
                            $bHasError = TRUE;
                        }
                    } elseif ($item->fix_exchange) {
                        $item->save();
                    }
                }

                if ($bHasError)
                    $trans->rollBack();
                else
                    $trans->commit();

            } catch (\Exception $e) {
                $trans->rollBack();
                $bHasError = TRUE;
                var_dump($e->getCode() . ' ' . $e->getMessage());
                if ($failCount > 0) {
                    sleep(2);
                    --$failCount;
                } else {
                    $bHasError = false;
                }
            }
        } while($bHasError);

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

    public function actionGetExchangeRatesByDate($date){
        $searchDate = strtotime($date);
        $oldDate = $searchDate - 86400;
        $curses = [];
        $updatedCurCodes = [2 => 933,1 => 840,11 => 978, 3 => 643];
        $resultNbrb = new ExchangeRatesNBRB();
        $resultCbrf = new ExchangeRatesCBRF();

        $curses['nbrb'] = $resultNbrb->getCurrencyRateByDate($searchDate);
        $curses['cbrf']= $resultCbrf->getCurrencyRateByDate($searchDate);

        $fromDb = true;

        $oldRates = ExchangeCurrencyHistory::find()->andWhere(['date'=>date('Y-m-d',$oldDate)])->all();
        $arCurrency = ExchangeRates::find()->all();
        $result = [];

        if(!empty($oldRates) && count($oldRates) == 12){
            foreach ($oldRates as $rate){
                $result['old_nbrb'][$rate->currency_id] = $rate->rate_nbrb;
                $result['old_cbrf'][$rate->currency_id] = $rate->rate_cbr;
            }
        }else{
            $fromDb = false;
            $curses['old_nbrb'] = $resultNbrb->getCurrencyRateByDate($oldDate);
            $curses['old_cbrf']= $resultCbrf->getCurrencyRateByDate($oldDate);


            foreach ($arCurrency as $cur){
                if(isset($curses['old_nbrb'][$updatedCurCodes[$cur->id]])){
                    $result['old_nbrb'][$cur->id] = $curses['old_nbrb'][$updatedCurCodes[$cur->id]];
                }else{
                    $result['old_nbrb'][$cur->id] = (float)1;
                }
                if(isset($curses['old_cbrf'][$cur->nbrb][$updatedCurCodes[$cur->id]])){
                    $result['old_cbrf'][$cur->id] = $curses['old_cbrf'][$updatedCurCodes[$cur->id]];
                }else{
                    $result['old_cbrf'][$cur->id] = (float)1;
                }
            }

        }

        foreach ($arCurrency as $cur){
            if(isset($updatedCurCodes[$cur->id]) && isset($curses['nbrb'][$updatedCurCodes[$cur->id]])){
                if($cur->id != 3){
                    $result['nbrb'][$cur->id] = $curses['nbrb'][$updatedCurCodes[$cur->id]];
                }else{
                    $result['nbrb'][$cur->id] = $curses['nbrb'][$updatedCurCodes[$cur->id]]/100;
                }
            }elseif(isset($updatedCurCodes[$cur->id])){
                $result['nbrb'][$cur->id] = (float)1;
            }elseif($cur->id == 13){
                $result['nbrb'][$cur->id] = $curses['nbrb'][$updatedCurCodes[3]]/100;
            }
            if(isset($updatedCurCodes[$cur->id]) && isset($curses['cbrf'][$cur->nbrb][$updatedCurCodes[$cur->id]])){
                $result['cbrf'][$cur->id] = $curses['cbrf'][$updatedCurCodes[$cur->id]];
            }elseif(isset($updatedCurCodes[$cur->id])){
                $result['cbrf'][$cur->id] = (float)1;
            }elseif($cur->id == 13){
                $result['cbrf'][$cur->id] = (float)1;
            }
        }


        foreach($arCurrency as $item)
        {
            if($item->use_base) //так как основные валюты уже обновили, обновим зависимые валюты
            {
                /** @var ExchangeRates $obBase */
                $obBase = ExchangeRates::findOne(['id' => $item->base_id]);
                if(empty($obBase))
                    throw new NotFoundHttpException('Base currency not found');

                $result['nbrb'][$item->id] = round($result['nbrb'][$obBase->id]*$item->factor,4);
                $result['cbrf'][$item->id] = round($result['cbrf'][$obBase->id]*$item->factor,4);

                if(!$fromDb){

                    $result['old_nbrb'][$item->id] = round($result['old_nbrb'][$obBase->id]*$item->factor,4);
                    $result['old_cbrf'][$item->id] = round($result['old_cbrf'][$obBase->id]*$item->factor,4);
                }


                $this->saveHistoryRate($item->id,$searchDate,$result);

            }elseif($item->use_exchanger) //обновим фиксированные валюты
            {
                $data = $this->parseObmennikByDate($searchDate);

                if(empty($data))
                    throw new NotFoundHttpException('Cant get currency from obmennik.by');

                $factor = empty($item->factor) ? 1 : $item->factor;

                $result['cbrf'][$item->id] = $data['rur']*$factor;
                $result['nbrb'][$item->id] = $data['usd']*$factor;

                $this->saveHistoryRate($item->id,$searchDate,$result);

            }elseif($item->fix_exchange){
                $result['nbrb'][$item->id] = $item->nbrb_rate;
                $result['cbrf'][$item->id] = $item->cbr_rate;

                if(!$fromDb){
                    $result['old_nbrb'][$item->id] = $item->nbrb_rate;
                    $result['old_cbrf'][$item->id] = $item->cbr_rate;
                }

                $this->saveHistoryRate($item->id,$searchDate,$result);
            }else{
                $this->saveHistoryRate($item->id,$searchDate,$result);
            }

        }
    }

    private function parseObmennikByDate($date){
        $url = 'http://obmennik.by/archivesbanksofbelarus.php?date='.date('Y-m-d',$date);
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        curl_close($ch);

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $dom->encoding = 'UTF-8';
        libxml_use_internal_errors($internalErrors);

        $tables = $dom->getElementsByTagName('table');

        $myTabbleRow = $tables->item(6)->getElementsByTagName('tr')->item(11)->getElementsByTagName('td');

        return ['usd' => $myTabbleRow->item(3)->textContent, 'rur' => (float)$myTabbleRow->item(7)->textContent/100];
    }

    private function saveHistoryRate($curId,$tmstmpDate, $curData){
        $date = date('Y-m-d',$tmstmpDate);
        $obH = ExchangeCurrencyHistory::findOne(['currency_id' => $curId,'date' => $date]);
        if(empty($obH))
            $obH = new ExchangeCurrencyHistory();

        $userID = NULL;
        $app = \Yii::$app;
        if(property_exists($app,'user') && !\Yii::$app->user->isGuest)
            $userID = \Yii::$app->user->id;

        $obH->currency_id = $curId;
        $obH->date = $date;
        $obH->user_id = $userID;
        $obH->old_rate_nbrb = $curData['old_nbrb'][$curId];
        $obH->old_rate_cbr = $curData['old_cbrf'][$curId];
        $obH->rate_nbrb = $curData['nbrb'][$curId];
        $obH->rate_cbr = $curData['cbrf'][$curId];
        //сохраняем историю
        if($obH->save()){
            echo 'save: '.$obH->currency_id."\n";
        }else{
            echo 'not saved: '.$obH->currency_id."\n";
        }
    }

    public function actionRecoveryExchangeRates()
    {
        $beginDate = '2016-07-01';                  //дата с которой начинаем восстановление
        $endDate = '2016-07-05';                    //дата на которой заканчиваем восстановление
        $arRowsForInsert = [];                      //массив с строками для вставки в базу данных

        /** @var ExchangeRates $arCurrency */
        $arCurrency = ExchangeRates::find()->where(['need_upd' => ExchangeRates::YES])->all();  //массив с валютами для обновления
        if(empty($arCurrency))
            return $this->log(TRUE);

        $arCurrencyHistoryTmp = ExchangeCurrencyHistory::find()                        //история обновления валют
        ->where(['currency_id' => ArrayHelper::getColumn($arCurrency,'id')])
            ->andWhere(['between', 'date', $beginDate, $endDate])
            ->orderBy(['date' => SORT_ASC])
            ->all();

        $arCurrHist = [];

        foreach ($arCurrencyHistoryTmp as $currTmp)
        {
            $arCurrHist[$currTmp->date][] = $currTmp;
        }






















    }

}