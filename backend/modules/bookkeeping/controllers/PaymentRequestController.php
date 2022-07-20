<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 7/21/15
 * Time: 11:34 PM
 */

namespace backend\modules\bookkeeping\controllers;


use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use backend\modules\bookkeeping\form\AddPaymentForm;
use backend\modules\bookkeeping\form\SetManagerContractorForm;
use common\components\notification\RedisNotification;
use common\components\payment\PaymentOperations;
use common\models\AbstractModel;
use common\models\CUser;
use common\models\CuserPreferPayCond;
use common\models\CUserRequisites;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\managers\PaymentsManager;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\Payments;
use common\models\PaymentsCalculations;
use common\models\search\PaymentRequestSearch;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PaymentRequestController extends AbstractBaseBackendController{

    /**
     * переопределяем права на контроллер и экшены
     * @return array
     */
    public function behaviors()
    {
        $tmp = parent::behaviors();
        $tmp['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
				[
                    'allow' => false,
                    'roles' => ['teamlead']
                ],
                [
                    'allow' => true,
                    'roles' => ['admin','bookkeeper','moder','sale']
                ]
            ]
        ];

        return $tmp;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PaymentRequestSearch();
        if(Yii::$app->user->can('only_manager'))
            $searchModel->managerID = Yii::$app->user->id;
		
		if(Yii::$app->user->can('teamlead_sale')) {
			$membersMap = BUser::getAllMembersMap();
			$membersMap = count($membersMap)>0 ? array_keys($membersMap): Yii::$app->user->id;
			$searchModel->managerID = $membersMap; # тут нужно указать ид людей из группы тимлида
		}
		
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,[PaymentRequest::tableName().'.status' => PaymentRequest::STATUS_NEW]);
        if(empty($searchModel->pay_date))
            $searchModel->pay_date = NULL;

        $arTotal = $searchModel->totalCount(Yii::$app->request->queryParams,[PaymentRequest::tableName().'.status' => PaymentRequest::STATUS_NEW]);

        $arRedisPaymentRequest = RedisNotification::getPaymentRequestListForUser(Yii::$app->user->id);

        $cuserDesc = empty($searchModel->cntr_id) ? '' : \common\models\CUser::findOne($searchModel->cntr_id)->getInfoWithSite();
        $buserDesc = empty($searchModel->owner_id) ? '' : BUser::findOne($searchModel->owner_id)->getFio();

        foreach($arTotal as &$total)
        {
            $total = Yii::$app->formatter->asDecimal($total);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'arRedisPaymentRequest' => $arRedisPaymentRequest,
            'cuserDesc' => $cuserDesc,
            'buserDesc' => $buserDesc,
            'arTotal' => $arTotal
        ]);
    }

    /**
     * @param $pID
     * @return string|Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAddPayment($pID)
    {
        $now = strtotime(Date('Y-m-d H:i:s'));
        if(($now > strtotime(Date('Y-m-d 00:00:00'))&& $now < strtotime(Date('Y-m-d 10:35:00'))))
        {
            throw new ForbiddenHttpException('Payment request updating is forbidden until 10:35 AM!');
        }

        /** @var PaymentRequest $modelP */
        $modelP = PaymentRequest::findOne($pID);
        if(empty($modelP))
            throw new NotFoundHttpException('Payment request not found');
        $modelP->callViewedEvent();
        if(!Yii::$app->user->can('adminRights') && ($modelP->manager_id != Yii::$app->user->id && $modelP->cuser->manager_id!=Yii::$app->user->id))
            throw new ForbiddenHttpException('You are not allowed to perform this action');

        if($modelP->status != PaymentRequest::STATUS_NEW)
        {
            Yii::$app->session->setFlash('error',Yii::t('app/book','Payment request already processed'));
            return $this->redirect(['/bookkeeping/default/index']);
        }

        $arCondVisible = [];

        $postPayments = Payments::find()->where(['post_payment'=>1, 'cuser_id'=>$modelP->cntr_id])->all();
        $postPayments = [];
        if(!Yii::$app->request->post('AddPaymentForm')) {


            $formModel = new AddPaymentForm(['fullSumm' => $modelP->pay_summ, 'service' => $modelP->service_id]);
            if(!empty($modelP->service_id))
            {
                $obCntrID = CUser::findOneByIDCached($modelP->cntr_id);

                if(empty($obCntrID))
                    throw new NotFoundHttpException('Contractor not found');

                //курс валюты на дату платежа
                $nCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$modelP->pay_date),$modelP->currency_id);
                $paySumm = (float)$modelP->pay_summ*$nCurr;
                $arCondVisible = PaymentCondition::getAppropriateConditions(
                    $modelP->service_id,
                    $modelP->legal_id,
                    $paySumm,
                    $obCntrID->is_resident,
                    $modelP->pay_date);
                if(Yii::$app->user->identity->allow_set_sale) {
                    if (PaymentsManager::isSaleWithService($modelP->service_id, $modelP->cntr_id, $modelP->pay_date))
                        $formModel->isSale = TRUE;
                }else {
                    if (PaymentsManager::isSale($modelP->cntr_id, $modelP->pay_date))
                        $formModel->isSale = TRUE;
                }


                /*
                $obPPC = CuserPreferPayCond::find()->where([    //дефолтное условие
                    'cuser_id' => $obCntrID->id,
                    'service_id' => $modelP->service_id
                ])->one();

                if($obPPC)
                {
                    $obCondition = PaymentCondition::find()->select('type')->where(['id' => $obPPC->cond_id])->one();
                    if(!$obCondition)
                        throw new NotFoundHttpException();
                    $formModel->condType = empty($obCondition->type) ? PaymentCondition::TYPE_USUAL : $obCondition->type;
                    $formModel->condID = $obPPC->cond_id;
                    if(!in_array($obPPC->cond_id,$arCondVisible))
                        $arCondVisible[] = $obPPC->cond_id;
                }
                */
            }
            $model = [$formModel];
        }
        else
        {
            $model = AbstractModel::createMultiple(AddPaymentForm::classname());
            AbstractModel::loadMultiple($model,Yii::$app->request->post());
            $valid = AbstractModel::validateMultiple($model);
            $validSumm = FALSE;
            $tmpSumm = 0;
            foreach($model as $m)
            {
                $tmpSumm+=$m->summ;
            }
            if(round($tmpSumm,2) != round($modelP->pay_summ,2)) {
                Yii::$app->session->setFlash('error', Yii::t('app/book', 'You have to spend all amout'));
            }
            else
                $validSumm = TRUE;
            if($valid &&  $validSumm)
            {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $modelP->manager_id = Yii::$app->user->id;
                    $modelP->save();
                    $bError = FALSE;
                    /** @var AddPaymentForm $p */
                    foreach($model as $p) // добавляем патежи
                    {
                        $obPay = new Payments([
                            'cuser_id' => $modelP->cntr_id,
                            'pay_date' => $modelP->pay_date,
                            'pay_summ' => $p->summ,
                            'currency_id' => $modelP->currency_id,
                            'service_id' => $p->service,
                            'legal_id' => $modelP->legal_id,
                            'description' => $p->comment,
                            'prequest_id' => $modelP->id,
                            'condition_id' => $p->condID,
                            'payment_order' => $modelP->payment_order,
                            'isSale' => $p->isSale,
                            'saleUser' => $p->saleUser,
                            'hide_act_payment' => $p->hide_act_payment,
                            'post_payment' => $p->post_payment
                        ]);

                        if(!$obPay->save())
                        {

                            $bError = TRUE;
                            break;
                        }

                        //производим рассчет по каждому платежу исходя из условия
                        /** @var PaymentCondition $obCond */
                        $obCond = PaymentCondition::findOne($p->condID);
                        if(empty($obCond))
                            throw new NotFoundHttpException("Condition not found");

                        //курс валюты на дату платежа
                        $nCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$modelP->pay_date),$modelP->currency_id);

                        if(is_null($nCurr))
                        {
                            $bError = TRUE;
                            break;

                        }

                        if(!isset($p->curr_val) || empty($p->curr_val)) {
                            $p->curr_val = 0;
                        }

                        //переведем сумму в бел рубли.
                        $paySumm = (float)$p->summ*(float)$nCurr;

                        $customProd = (float)$p->customProduction*(float)$nCurr;

                        //расчет по бел рублям
                        $obOp = new PaymentOperations($paySumm,$obCond->tax,$obCond->commission,$obCond->corr_factor,$obCond->sale,$p->condType,$customProd);
                        $arCount = $obOp->getFullCalculate();

                        $obPayCalc = new PaymentsCalculations([
                            'payment_id' => $obPay->id,
                            'pay_cond_id' => $obCond->id,
                            'tax' => $arCount['tax'],
                            'profit' => $arCount['profit'],
                            'production' => $arCount['production'],
                            'cnd_corr_factor' => $obCond->corr_factor,
                            'cnd_commission' => $obCond->commission,
                            'cnd_sale' => $obCond->sale,
                            'cnd_tax' => $obCond->tax,
                            'custom_curr'=>$p->curr_val,
                            'profit_for_manager' => $arCount['profit'] - ($paySumm* PaymentsCalculations::COEF_FOR_PROFIT_MANAGER),
                        ]);

                        if(!$obPayCalc->save())
                        {
                            $bError = TRUE;
                            break;
                        }

                        $obPay->callSaveDoneEvent();
                        unset($obPay,$obPayCalc,$obCond,$obOp);

                    }

                    if(!$bError)
                    {
                        $modelP->status = PaymentRequest::STATUS_FINISHED;
                        $modelP->manager_id = Yii::$app->user->id;
                        $modelP->save();
                        $transaction->commit();
                        Yii::$app->session->set('success',Yii::t('app/book','Payments added successfully!'));
                        return $this->redirect(['/bookkeeping/default/index']);
                    }
                    $transaction->rollBack();
                    Yii::$app->session->set('error',Yii::t('app/book','Can not add payments!'));
                }catch(Exception $e){
                    $transaction->rollBack();
                    Yii::$app->session->set('error',Yii::t('app/book','Can not add payments!'));
                }
            }
        }

        return $this->render('add_payment',[
            'model' => $model,
            'modelP' => $modelP,
            'postPayments' => $postPayments,
            'arCondVisible' => $arCondVisible
        ]);
    }

    /**
     * @param $pID
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionPinPaymentToManager($pID)
    {
        /** @var PaymentRequest $modelP */
        $modelP = PaymentRequest::find()
            ->where(['id' => $pID])
            ->one();
        if(empty($modelP))
            throw new NotFoundHttpException('Payment request not found');
        $modelP->callViewedEvent();

        if(!empty($modelP->cntr_id))
        {
            $obCUser = CUser::findOne($modelP->cntr_id);
            if(empty($obCUser))
                throw new NotFoundHttpException('Contractor not found');
            if($obCUser->manager_id == Yii::$app->user->id)
            {
                $modelP->manager_id = Yii::$app->user->id;
                if($modelP->save()) {
                    $modelP->callEventPinManager();
                    Yii::$app->session->setFlash('success', Yii::t('app/book', 'Payment successfully pined'));
                }
                else
                    Yii::$app->session->setFlash('error',Yii::t('app/book','Error. Cant save the model;'));
            }else{
                Yii::$app->session->setFlash('error',Yii::t('app/book','Error. Contractor has another manager. Please tell about this payment another manager;'));
            }
            return $this->redirect(['index']);
        }

        $arContr = CUser::getContractorForManager(Yii::$app->user->id);
        $arContrMap = [];
        foreach($arContr as $ac)
        {
            $arContrMap[$ac['id']] = CUser::getInfoByArData($ac);
        }

        $model = new SetManagerContractorForm(['obPR' => $modelP,'contractorMap' => $arContrMap]);
        if($model->load(Yii::$app->request->post()) && $model->makeRequest())
        {
            $modelP->callEventPinManager();
            Yii::$app->session->setFlash('success',Yii::t('app/book','Payment successfully pined'));
            return $this->redirect(['index']);
        }
        return $this->render('pin_payment_to_manager',[
            'model' => $model,
            'arContrMap' => $arContrMap
        ]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        /** @var PaymentRequest $model */
        $model = PaymentRequest::find()
            ->where(['id' => $id])
            ->with('owner','legal','currency','cuser')
            ->one();
        $model->callViewedEvent();
        return $this->render('view',[
            'model' => $model
        ]);
    }

    /**
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionFindCondition()
    {
        $iServID = Yii::$app->request->post('iServID');
        $iContrID = Yii::$app->request->post('iContrID');
        $lPID = Yii::$app->request->post('lPID');
        $amount = Yii::$app->request->post('amount');
        $prID = (int)Yii::$app->request->post('prID');

        $obCntrID = CUser::findOneByIDCached($iContrID);

        if(empty($obCntrID))
            throw new NotFoundHttpException('Contractor not found');

        $modelP = PaymentRequest::findOne($prID);
        if(empty($modelP))
            throw new NotFoundHttpException('Payment request not found');

        $nCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$modelP->pay_date),$modelP->currency_id);
        $paySumm = (float)$amount*$nCurr;
        $arCondVisible = PaymentCondition::getAppropriateConditions(
            $iServID,
            $modelP->legal_id,
            $paySumm,
            $obCntrID->is_resident,
            $modelP->pay_date);
        /*
         * //дефолтное условие
        $obPPC = CuserPreferPayCond::find()->where([    //дефолтное условие
            'cuser_id' => $obCntrID->id,
            'service_id' => $iServID
        ])->one();

        if(!empty($obPPC) && !in_array($obPPC->cond_id,$arCondVisible))
            $arCondVisible [] = $obPPC->cond_id;
        */
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['visable' => $arCondVisible,'default' => empty($obPPC) ? NULL : $obPPC->cond_id];
    }

    /**
     * @param $iServID
     * @param $lPID
     * @param $obCntrID
     * @return array
     */
    protected function getCondition($iServID,$lPID,$obCntrID,$amount = 0)
    {
        /*
        $obPPC = CuserPreferPayCond::find()->where([    //дефолтное условие
            'cuser_id' => $obCntrID->id,
            'service_id' => $iServID
        ])->one();

        if($obPPC)
            return ['cID' => $obPPC->cond_id];

        unset($obPPC);
        */

        $obCond = PaymentCondition::find()  //находим все условия
        ->select(['id','summ_from','summ_to'])
            ->where([
                'service_id' => (int)$iServID,
                'l_person_id' => (int)$lPID,
                'is_resident' => (int)$obCntrID->is_resident
            ])
            ->orderBy('id DESC')
            ->all();

        if(empty($obCond))
            return ['cID' =>  FALSE ];

        foreach($obCond as $cond)
        {
            if($cond->summ_from <= $amount && $cond->summ_to > $amount)
            {
                return ['cID' => $cond->id];
            }
        }

        return ['cID' => FALSE];
    }

    /**
     * @param $id
     * @return Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $model = PaymentRequest::findOne(['id' => $id,'status' => PaymentRequest::STATUS_NEW]);
        if(empty($model))
            throw new NotFoundHttpException('Payment request not found');

        if($model->owner_id != Yii::$app->user->id)
            throw new ForbiddenHttpException('You are not allowed to perform this action.');

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        $now = strtotime(Date('Y-m-d H:i:s'));
        if(($now > strtotime(Date('Y-m-d 00:00:00'))&& $now < strtotime(Date('Y-m-d 10:35:00'))))
        {
            throw new ForbiddenHttpException('Payment request updating is forbidden until 10:35 AM!');
        }

        $model = PaymentRequest::findOne(['id' => $id,'status' => PaymentRequest::STATUS_NEW]);
        if(empty($model))
            throw new NotFoundHttpException('Payment request not found');

        $model->updateNotifications = TRUE;
        if(!Yii::$app->user->can('adminRights') && $model->owner_id != Yii::$app->user->id)
            throw new ForbiddenHttpException('You are not allowed to perform this action.');

        if($model->load(Yii::$app->request->post()))
        {
            $model->bank_id = isset($model->bank[$model->legal_id])?$model->bank[$model->legal_id]:null;
            if($model->save())
            {
                Yii::$app->session->setFlash('success',Yii::t('app/book','Payments request successfully updated'));
                return $this->redirect(['index']);
            }
        }

        return $this->render('update',[
            'model' => $model
        ]);
    }

    /**
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionBoundsCheckingConditions()
    {
        $iCondID = Yii::$app->request->post('iCondID');     // ID условия
        $iSumm = (float)Yii::$app->request->post('iSumm');     //сумма платежа
        $iCurr = (int)Yii::$app->request->post('iCurr');     //Валюта платежа
        $payDate = Yii::$app->request->post('payDate');

        if(!empty($iCurr))  //если указана валюта платежа, то переведем в бел. рубли.
        {
            $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$payDate),$iCurr); //курс валюты на дату платежа для самого платежа
            if(is_null($curr))
                throw new NotFoundHttpException('Currency not found');

            $iSumm = (float)$iSumm*(float)$curr;
        }

        /** @var PaymentCondition $obCond */
        $obCond = PaymentCondition::findOneByIDCached($iCondID);    //получаем условие
        if(!$obCond)
            throw new NotFoundHttpException('Condition not found');

        //курс валюты для условия
        $currCond = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$payDate),$obCond->currency_id); //курс валюты на дату платежа

        if(!$currCond)
            throw new NotFoundHttpException('Currency not found');

        $iLeftSumm = (float)$obCond->summ_from*(float)$currCond;    //переводим в бел. рубли. Левая граница
        $iRightSumm = (float)$obCond->summ_to*(float)$currCond;     //переводим в бел. рубли. Правая граница

        Yii::$app->response->format = Response::FORMAT_JSON;        //указываем,что возвращать будем в JSON
        if($iLeftSumm > $iSumm || $iSumm > $iRightSumm)    //соответсвует ли сумма границам.
            return TRUE;
        else
            return FALSE;
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     */
    public function actionIsSale()
    {
        $iServID = Yii::$app->request->post('iServID');     //Услуга
        $iContrID = Yii::$app->request->post('iContrID');   //контрагент
        $payDate = Yii::$app->request->post('payDate');     //дата платежа

        if(empty($iServID) || empty($iContrID) || empty($payDate))
            throw new InvalidParamException();

        Yii::$app->response->format = Response::FORMAT_JSON;
        if(Yii::$app->user->identity->allow_set_sale)
            return PaymentsManager::isSaleWithService($iServID, $iContrID, $payDate);
        else
            return PaymentsManager::isSale($iContrID, $payDate);
    }
} 