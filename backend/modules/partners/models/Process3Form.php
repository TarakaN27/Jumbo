<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.4.16
 * Time: 13.58
 */

namespace backend\modules\partners\models;


use common\models\EnrollmentRequest;
use common\models\Expense;
use common\models\PartnerExpenseCatLink;
use common\models\Services;
use yii\base\Model;

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
            [['serviceID','amount','legalPersonID','contractorID'],'required'],
            ['description','string','max' => 255],
            ['amount','number'],
            [['serviceID','legalPersonID','contractorID'],'integer']
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
            'contractorID' => \Yii::t('app/users','Contractor ID')
        ];
    }

    /**
     *
     */
    public function makeRequest()
    {
        if(!$this->saveExpense())
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
            return FALSE;

        $obCat = PartnerExpenseCatLink::getCatByServAndLP($this->serviceID,$this->legalPersonID,PartnerExpenseCatLink::TYPE_SERVICES);
        if(!$obCat)
        {
            $this->arCustomErrors [] = \Yii::t('app/users','Category expense not found!');
            return FALSE;
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

        return $obExpense->save();
    }

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

        $obEnrollReq = new EnrollmentRequest();
        $obEnrollReq->amount = $this->countAmoutForEnrollment($model,$obCond,$obCalc);
        $obEnrollReq->service_id = $obSrv->id;
        $obEnrollReq->assigned_id = $obSrv->b_user_enroll;
        $obEnrollReq->payment_id = $model->id;
        $obEnrollReq->cuser_id = $model->cuser_id;
        $obEnrollReq->pay_amount = $model->pay_summ;
        $obEnrollReq->pay_currency = $model->currency_id;
        $obEnrollReq->pay_date = $model->pay_date;
        $obEnrollReq->status = EnrollmentRequest::STATUS_NEW;
        $obEnrollReq->added_by = \Yii::$app->user->id;
        return $obEnrollReq->save();
    }
}