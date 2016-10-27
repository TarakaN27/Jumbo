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
use common\models\Acts;
use common\models\BonusScheme;
use common\models\BUserBonus;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use common\models\Payments;
use console\components\AbstractConsoleController;
use yii\console\Controller;
use common\components\crunchs\denomination\Denomination;
use common\components\payment\PaymentBonusBehavior;
use common\components\acts\ActsDocumentsV2;

class RecalculateController extends AbstractConsoleController
{
    /**
     * @return int
     */
    public function actionDenomination(){
        $denomination = new Denomination();
        $denomination->run();

    }

    public function actionProfitBonus(){
        $payments = Payments::find()->andWhere(['>=','pay_date', strtotime("2016-10-01")])->all();
        $schemes = BonusScheme::find()->where(['type'=>BonusScheme::TYPE_PROFIT_PAYMENT])->all();
        foreach($schemes as $item){
            foreach($item->users as $user){
                $bonus = BUserBonus::find()->joinWith('payment')->where(['buser_id'=>$user->id])->andWhere(['>=','pay_date',strtotime("2016-10-01")])->all();
                foreach($bonus as $temp)
                    $temp->delete();
            }
        }
        foreach($payments as $payment) {
            $obCount = new PaymentBonusBehavior();
            $obCount->countingProfitBonus($payment);
        }
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

    public function actionRegenerateActs(){
        $acts = Acts::find()->all();
        foreach($acts as $act){
            $obActDoc = new ActsDocumentsV2($act->id,$act->lp_id,$act->cuser_id,$act->act_date,$act->act_num,$act->currency_id);
            $fileName = $obActDoc->generateDocument();
            if(!$fileName)
                throw new Exception();

            $act->genFile = Acts::YES;
            $act->file_name = $fileName;
            if(!$act->save())
                throw new ServerErrorHttpException();
        }
    }
}