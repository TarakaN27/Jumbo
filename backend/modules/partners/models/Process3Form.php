<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.4.16
 * Time: 13.58
 */

namespace backend\modules\partners\models;


use common\models\EnrollmentRequest;
use common\models\ExchangeCurrencyHistory;
use common\models\Expense;
use common\models\PartnerExpenseCatLink;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use common\models\PaymentCondition;
use common\models\Services;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class Process3Form extends Model
{
    public
        $conditionID = NULL,
        $arCustomErrors = [],
        $obRequest = NULL,
        $serviceID = NULL,
        $amount = NULL,
        $legalPersonID = NULL,
        $contractorID = NULL,
        $description = NULL;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['serviceID','amount','legalPersonID','contractorID','conditionID'],'required'],
            ['description','string','max' => 255],
            ['amount','number'],
            [['serviceID','legalPersonID','contractorID','conditionID'],'integer']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'serviceID' => \Yii::t('app/users','Service'),
            'amount' => \Yii::t('app/users','Amount'),
            'legalPersonID' => \Yii::t('app/users','Legal person'),
            'description' => \Yii::t('app/users','Description'),
            'contractorID' => \Yii::t('app/users','Contractor ID'),
            'conditionID' => \Yii::t('app/users','Condition ID')
        ];
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function makeRequest()
    {
        if(null == $obExpenceID = $this->saveExpense())         //expanse
            return FALSE;

        if(!$this->createEnrollment())                          //enrollment
            return FALSE;

        if(!$this->partnerPurseOperation($obExpenceID))         //partner purse
            return FALSE;

        return TRUE;
    }

    /**
     * Save expense
     * @return bool
     */
    protected function saveExpense()
    {
        if(!$this->obRequest)
            return NULL;

        $obCat = PartnerExpenseCatLink::getCatByServAndLP($this->serviceID,$this->legalPersonID,PartnerExpenseCatLink::TYPE_SERVICES);
        if(!$obCat)
        {
            $this->arCustomErrors [] = \Yii::t('app/users','Category expense not found!');
            return NULL;
        }

        $obExpense = new Expense();
        $obExpense->cat_id = $obCat->id;
        $obExpense->currency_id = $this->obRequest->currency_id;
        $obExpense->cuser_id = $this->contractorID;
        $obExpense->legal_id = $this->legalPersonID;
        $obExpense->pay_date = time();
        $obExpense->pay_summ = $this->obRequest->amount;
        $obExpense->description = $this->description;
        $obExpense->pw_request_id = $this->obRequest->id;

        if($obExpense->save())
            return $obExpense->id;

        return NULL;
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     */
    protected function createEnrollment()
    {
        /** @var Services $obServ */
        $obServ = Services::find()
            ->select([
                'id',
                'name',
                'allow_enrollment',
                'b_user_enroll'
            ])
            ->where(['id' => $this->serviceID])
            ->one();

        if(!$obServ)
        {
            $this->arCustomErrors [] = \Yii::t('app/users','Service not found!');
            return FALSE;
        }

        if(!$obServ->allow_enrollment)
            return TRUE;

        if(!$obServ->b_user_enroll)
        {
            $this->arCustomErrors [] = \Yii::t('app/users','Empty responsibility for enrollment!').$obServ->name;
            return FALSE;
        }

        $obCond = PaymentCondition::find()->where(['id' => $this->conditionID])->one();
        if(!$obCond)
            throw new NotFoundHttpException('Condition not found');

        $pCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$this->obRequest->date),$this->obRequest->currency_id);
        if(!$pCurr)
            throw new NotFoundHttpException('Currency not found');

        $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$this->obRequest->date),$obCond->cond_currency);

        if(!$curr)
            throw new NotFoundHttpException('Currency not found');

        $amount = round($this->amount*$pCurr/$curr,6);

        $obEnrollReq = new EnrollmentRequest();
        $obEnrollReq->amount = $amount;
        $obEnrollReq->service_id = $this->serviceID;
        $obEnrollReq->assigned_id = $obServ->b_user_enroll;

        $obEnrollReq->cuser_id = $this->obRequest->partner_id;
        $obEnrollReq->pay_amount = $this->amount;
        $obEnrollReq->pay_currency = $this->obRequest->currency_id;
        $obEnrollReq->pay_date = $this->obRequest->date;
        $obEnrollReq->pw_request_id = $this->obRequest->id;
        $obEnrollReq->status = EnrollmentRequest::STATUS_NEW;
        $obEnrollReq->added_by = \Yii::$app->user->id;
        return $obEnrollReq->save();
    }

    /**
     * @param $iExpenseID
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function partnerPurseOperation($iExpenseID)
    {
        $pCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$this->obRequest->date),$this->obRequest->currency_id);
        if(!$pCurr)
            throw new NotFoundHttpException('Currency not found');

        $amount = $this->amount*$pCurr;

        $obPurseHistory = new PartnerPurseHistory();
        $obPurseHistory->amount = $amount;
        $obPurseHistory->type = PartnerPurseHistory::TYPE_EXPENSE;
        $obPurseHistory->cuser_id = $this->obRequest->partner_id;
        $obPurseHistory->expense_id = $iExpenseID;
        if(!$obPurseHistory->save())
            throw new ServerErrorHttpException('Can not save purse history');

        
        $obPurse = PartnerPurse::getPurse($this->obRequest->partner_id);
        $obPurse->withdrawal+=$amount;
        if(!$obPurse->save())
            throw new ServerErrorHttpException('Can not save purse');

        return TRUE;
    }
}