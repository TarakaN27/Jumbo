<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.4.16
 * Time: 12.16
 */

namespace console\controllers;


use common\components\crunchs\bonus\RecalculateBonus;
use common\components\crunchs\Payment\RecalcQuantityHours;
use common\components\partners\PartnerPercentCounting;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use console\components\AbstractConsoleController;
use yii\console\Controller;
use common\components\crunchs\denomination\Denomination;
use yii\helpers\ArrayHelper;
use common\models\ExchangeCurrencyHistory;

class RecalculateController extends AbstractConsoleController
{
    /**
     * @return int
     */
    public function actionDenomination(){
        $denomination = new Denomination();
        $denomination->run();

    }

    public function actionQuantityHours()
    {
        $obQantity = new RecalcQuantityHours();
        $obQantity->run();
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * @return int
     */
    public function actionBonus()
    {
        $obRecalc = new RecalculateBonus();
        $obRecalc->run();
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Recalculate partner manager bonus
     * @return int
     */
    public function actionBonusPartner()
    {
        $obRecalc = new RecalculateBonus();
        $obRecalc->recalculatePartnerBonus();
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * @param $date
     * @return int
     */
    public function actionCountingPartnerPercent($date=NULL)
    {
        $obCalc = new PartnerPercentCounting();
        $obCalc->countPercentByMonth($date);
        return Controller::EXIT_CODE_NORMAL;
    }

    public function actionAllPartnersPercent(){
        $date = new \DateTime("2016-01-01");
        $now = date('Y-m').'-01';
        PartnerPurseHistory::deleteAll(['type'=>PartnerPurseHistory::TYPE_INCOMING]);
        PartnerPurse::deleteAll();
        $withdrowalHistory = PartnerPurseHistory::findAll(['type'=>PartnerPurseHistory::TYPE_EXPENSE]);
        while($date->format('Y-m-d')<=$now){
            $obCalc = new PartnerPercentCounting();
            //бовтрутенко считаем только с 01.06.2016
            $obCalc->excludePartnerPeriod[8869] = '2016-06-01';
			//зубарева с 01.08.2016
            $obCalc->excludePartnerPeriod[8859] = '2016-08-01';
            
            $obCalc->countPercentByMonth($date->format('Y-m-d'));
            $date->modify("+1 month");
        }
        foreach($withdrowalHistory as $item){
            $purse = PartnerPurse::findOne(['cuser_id'=>$item->cuser_id]);
            $purse->amount -= $item->amount;
            $purse->save();
        }

    }
}