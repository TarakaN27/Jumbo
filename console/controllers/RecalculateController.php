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
use common\components\payment\PaymentEnrollmentBehavior;
use common\models\Acts;
use common\models\BonusScheme;
use common\models\BUserBonus;
use common\models\EnrollmentRequest;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\Payments;
use common\models\PaymentsCalculations;
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
        $payments = Payments::find()->andWhere(['>=','pay_date',strtotime("2017-01-01")])->all();
        $schemes = BonusScheme::find()->where(['type'=>BonusScheme::TYPE_PROFIT_PAYMENT])->all();
        foreach($schemes as $item){
            foreach($item->users as $user){
                $bonus = BUserBonus::find()->joinWith('payment')->where(['buser_id'=>$user->id])->andWhere(['>=','pay_date',strtotime("2017-01-01")])->all();
                foreach($bonus as $temp)
                    $temp->delete();
            }
        }
        foreach($payments as $payment) {
            $obCount = new PaymentBonusBehavior();
            if($payment->sale)
                $payment->isSale = 1;
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
        $acts = Acts::find()->andWhere(['>=','act_date', '2017-03-06'])->andWhere(['<=','act_date', '2017-03-31'])->all();
        foreach($acts as $act){
            $obActDoc = new ActsDocumentsV2($act->id,$act->lp_id,$act->cuser_id,$act->act_date,$act->act_num,$act->currency_id, $act->bank_id);
            $fileName = $obActDoc->generateDocument();
            if(!$fileName)
                throw new Exception();

            $act->genFile = Acts::YES;
            $act->file_name = $fileName;
            if(!$act->save())
                throw new ServerErrorHttpException();
        }
    }

    public function actionChangeCondition(){
        $enrollmentRequest = EnrollmentRequest::find()->where(['payment_id'=>[4198,4036,4046,4099,4144,4170,4200,4206,4223]])->all();
        foreach($enrollmentRequest as $item){
            $cacl = $item->payment->calculate;
            $enrollBehavior = new PaymentEnrollmentBehavior();
            $item->amount = $enrollBehavior->countAmoutForEnrollment($item->payment, $cacl->payCond, $cacl);
            $item->enroll_unit_id = $cacl->payCond->enroll_unit_id;
            $item->save();
        }
    }
    public function actionTest(){
        $legalPerson = \common\models\LegalPerson::find()->where(['disallow_create_bill'=>0])->orderBy(['id' => SORT_ASC])->all();
        foreach($legalPerson as $item){
            \common\models\PartnerWBookkeeperRequest::updateAll(['bank_id'=>$item->default_bank_id],['legal_id'=>$item->id]);
        }
    }

}