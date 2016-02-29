<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 2/24/16
 * Time: 4:26 PM
 */

namespace backend\modules\bookkeeping\form;


use backend\widgets\Alert;
use common\models\EnrollmentRequest;
use common\models\Enrolls;
use common\models\PromisedPayment;
use common\models\PromisedPayRepay;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class EnrollProcessForm extends Model{

    public
        $part_enroll,
        $availableAmount,
        $description,
        $isPayment,
        $request,   //сам запрос
        $arPromised = [],
        $enroll,    //зачислено
        $repay = 0;     //погашено


    public function rules()
    {
        return [
            [['description'],'string','max' => 255],
            [['isPayment','part_enroll'],'integer'],
            [['enroll','repay','availableAmount'],'number'],
    //        ['enroll','validateAmount']
        ];
    }

    public function attributeLabels()
    {
        return [
            'enroll' => \Yii::t('app/book','Unit enroll amount'),
            'repay' => \Yii::t('app/book','Unit repay amount'),
            'description' => \Yii::t('app/book','Description'),
            'part_enroll' => \Yii::t('app/book','Partial enrollment')
        ];
    }

    /**
     * @param $attribute
     * @param $param
     */
    public function validateAmount($attribute,$param)
    {
        if(($this->enroll+$this->repay) > $this->availableAmount)
            $this->addError($attribute,\Yii::t('app/book','Summ of Enroll and repay must be less or equal available amount'));

    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function makeRequest()
    {
        $tr = \Yii::$app->db->beginTransaction();
        try {
            $obEnroll = new Enrolls();
            $obEnroll->buser_id = \Yii::$app->user->id;
            $obEnroll->cuser_id = $this->request->cuser_id;
            $obEnroll->service_id = $this->request->service_id;
            $obEnroll->amount = $this->availableAmount;
            $obEnroll->repay = $this->repay;
            $obEnroll->enroll = $this->enroll;
            $obEnroll->enr_req_id = $this->request->id;
            $obEnroll->description = $this->description;
            if(!$obEnroll->save())
                throw new ServerErrorHttpException();

            if($this->part_enroll)  //частичное зачисление
            {
                $partAmount = $this->availableAmount - (float)$this->enroll - (float)$this->enroll;
                if($partAmount > 0)
                {
                    /** @var EnrollmentRequest $obRequest */
                    $obRequest = clone $this->request;
                    $obRequest->id = NULL;
                    $obRequest->isNewRecord = TRUE;
                    $obRequest->amount = $partAmount;
                    $obRequest->parent_id = $this->request->id;
                    if(!$obRequest->save())
                    {
                        throw new ServerErrorHttpException();
                    }
                }else{
                    \Yii::$app->session->setFlash(Alert::TYPE_ERROR,\Yii::t('app/book','Not enough amount for create partition request'));
                }
            }

            if ($this->repay > 0 && $this->isPayment)    //гасим обещанные платежи платежи
            {
                $rAmount = $this->repay;
                /** @var PromisedPayment $pro */
                foreach ($this->arPromised as $pro) {
                    if ($pro->paid == PromisedPayment::YES)
                        throw new InvalidParamException();

                    if ($rAmount <= 0)
                        break;

                    if ($rAmount >= $pro->amount) {
                        $rAmount -= $pro->amount;
                        $pro->paid = PromisedPayment::YES;
                        $pro->buser_id_p = \Yii::$app->user->id;
                        $pro->paid_date = time();
                        if(!$pro->save())
                            throw new ServerErrorHttpException();
                        $obRep = new PromisedPayRepay();
                        $obRep->enroll_id = $obEnroll->id;
                        $obRep->pr_pay_id = $pro->id;
                        $obRep->amount = $pro->amount;
                        $obRep->payment_id = $this->request->payment_id;
                        if(!$obRep->save())
                            throw new ServerErrorHttpException();
                    } else {
                        $obRep = new PromisedPayRepay();
                        $obRep->enroll_id = $obEnroll->id;
                        $obRep->pr_pay_id = $pro->id;
                        $obRep->amount = $rAmount;
                        $obRep->payment_id = $this->request->payment_id;
                        if(!$obRep->save())
                            throw new ServerErrorHttpException();
                        $rAmount = 0;
                    }
                }
            }

            //редактируем текущий запрос
            $this->request->status = EnrollmentRequest::STATUS_PROCESSED;
            if($this->part_enroll)  //частичное зачисление
                $this->request->part_enroll = $this->part_enroll;
            if(!$this->request->save())
                throw new ServerErrorHttpException();

            $tr->commit();
            return TRUE;
        }catch (Exception $e){
            $tr->rollBack();
            return FALSE;
        }
    }
} 