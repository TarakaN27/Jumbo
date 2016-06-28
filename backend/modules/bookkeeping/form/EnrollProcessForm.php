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
use common\models\CuserToGroup;
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
        $cuserOP,
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
            [['isPayment','part_enroll','cuserOP'],'integer'],
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
            'part_enroll' => \Yii::t('app/book','Partial enrollment'),
            'cuserOP' => \Yii::t('app/book','Cuser for OP')
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
                $partAmount = (float)$this->availableAmount - (float)$this->enroll - (float)$this->repay;
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
                $arUserGroup = $arUserID  = CuserToGroup::getAllUserIdsAtGroupByUserId((int)$this->request->cuser_id);
                $orderType = SORT_ASC;
                if(!empty($this->cuserOP)) {
                    $arUserID [] = $this->cuserOP;
                    if($this->cuserOP < $this->request->cuser_id)
                        $orderType = SORT_DESC;
                }

                $arUserID = array_unique($arUserID);

                $arPromised = PromisedPayment::find()
                    ->where([
                        'cuser_id' => $arUserID,
                        'service_id' => $this->request->service_id
                    ])
                    ->andWhere('(paid is NULL OR paid = 0)');

                if(!empty($arGroupUsers))
                {
                    $arPromised->orderBy(['FIELD(cuser_id,'.implode(',',$arGroupUsers).')' => SORT_DESC]);      //вначе идут ОП контрагента и его группы, затем чужие
                }else{
                    $arPromised->orderBy(['cuser_id' => $orderType]);                   //нет группы
                }
                    //->orderBy(['cuser_id' => $orderType])
                    //->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
                $arPromised = $arPromised->all();

                $arPIds = [];
                foreach($arPromised as $pr)
                    $arPIds [] = $pr->id;

                $arRepayTmp = PromisedPayRepay::find()
                    ->select(['amount','pr_pay_id'])
                    ->where(['pr_pay_id' => $arPIds])
                    ->all();

                $arRepay = [];
                foreach($arRepayTmp as $tmp)
                    if(isset($arRepay[$tmp->pr_pay_id]))
                        $arRepay[$tmp->pr_pay_id]+=$tmp->amount;
                    else
                        $arRepay[$tmp->pr_pay_id]=$tmp->amount;

                /** @var PromisedPayment $pro */
                foreach ($arPromised as $pro) {
                    if ($pro->paid == PromisedPayment::YES)
                        throw new InvalidParamException();

                    if ($rAmount <= 0)
                        break;

                    $repay = isset($arRepay[$pro->id]) ? $arRepay[$pro->id] : 0;

                    if ($rAmount >= ($pro->amount - $repay)) {
                        $rAmount -= ($pro->amount - $repay);
                        $pro->paid = PromisedPayment::YES;
                        $pro->buser_id_p = \Yii::$app->user->id;
                        $pro->paid_date = time();
                        if(!$pro->save())
                            throw new ServerErrorHttpException();
                        $obRep = new PromisedPayRepay();
                        $obRep->enroll_id = $obEnroll->id;
                        $obRep->pr_pay_id = $pro->id;
                        $obRep->amount = ($pro->amount - $repay);
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