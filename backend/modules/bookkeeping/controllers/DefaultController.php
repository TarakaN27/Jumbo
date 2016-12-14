<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\components\payment\PaymentBonusBehavior;
use common\components\payment\PaymentEnrollmentBehavior;
use common\components\payment\PaymentOperations;
use common\models\Acts;
use common\models\ActToPayments;
use common\models\CUser;

use common\models\CuserPreferPayCond;
use common\models\CUserRequisites;
use common\models\Dialogs;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\PaymentsCalculations;

use Yii;
use common\models\Payments;
use common\models\search\PaymentsSearch;

use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use backend\modules\bookkeeping\form\MigrateLoadFileForm;

/**
 * DefaultController implements the CRUD actions for Payments model.
 */
class DefaultController extends AbstractBaseBackendController
{

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
                    'actions' => ['index','view','create','update','get-conditions','find-condition'],
                    'allow' => true,
                    'roles' => ['moder']
                ],
                [
                    'allow' => true,
                    'roles' => ['admin','bookkeeper']
                ]
            ]
        ];
        $tmp['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'delete' => ['post'],
                'get-manager' => ['post'],
                'find-contact-number' => ['post']
            ],
        ];

        return $tmp;
    }

    /**
     * Lists all Payments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $arTotal = $searchModel->totalCount(Yii::$app->request->queryParams);

        if(empty($searchModel->pay_date))
            $searchModel->pay_date = NULL;

        $cuserDesc = empty($searchModel->cuser_id) ? '' : \common\models\CUser::findOne($searchModel->cuser_id)->getInfoWithSite();
        $buserDesc = empty($searchModel->manager) ? '' : BUser::findOne($searchModel->manager)->getFio();

        foreach($arTotal as &$total)
        {
            $total = Yii::$app->formatter->asDecimal($total);
        }

        $arPaymentsIds = ArrayHelper::getColumn($dataProvider->getModels(),'id');
        $arActsTmp = ActToPayments::getRecordsByPaymentsId($arPaymentsIds);
        $arActs = [];
        foreach ($arActsTmp as $key => $actTmp)
        {
            foreach ($actTmp as $item)
                if(isset($arActs[$key]))
                    $arActs[$key]+=(float)$item->amount;
                else
                    $arActs[$key]=(float)$item->amount;
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cuserDesc' => $cuserDesc,
            'buserDesc' => $buserDesc,
            'arTotal' => $arTotal,
            'arActs' => $arActs
        ]);
    }

    public function actionLoadXml()
    {
        $model = new MigrateLoadFileForm();
        if (Yii::$app->request->isPost) {
            if(isset($_FILES['MigrateLoadFileForm']) && $_FILES['MigrateLoadFileForm']['tmp_name']){
                $models = $this->parseXml($_FILES['MigrateLoadFileForm']['tmp_name']['src']);
                if($models) {
                    return $this->render('migrate_form_list', [
                        'models' => $models,
                    ]);
                }else {
                    Yii::$app->session->setFlash('danger', Yii::t('app/book', 'Dont have payments for loading'));
                    return $this->redirect(['index']);
                }
            }else{
                $savedModels = [];
                $notSavedmodels = [];
                foreach(Yii::$app->request->post('PaymentRequest') as $item){
                    $model = new PaymentRequest($item);
                    $model->owner_id = Yii::$app->user->id;
                    $model->status = PaymentRequest::STATUS_NEW;
                    if($model->active) {
                        if ($model->validate()) {
                            $model->save(false);
                            $obDlg = new Dialogs();
                            $obDlg->type = Dialogs::TYPE_REQUEST;
                            $obDlg->buser_id = Yii::$app->user->id;
                            $obDlg->status = Dialogs::PUBLISHED;
                            $obDlg->theme = Yii::t('app/book','New payment request').'<br>'.$model->description;
                            if($obDlg->save())
                            {
                                if(!empty($model->manager_id))//если указан менеджер, то добавляем его к диалогу
                                {
                                    $obManager = BUser::findOne($model->manager_id);
                                    if(empty($obManager))
                                        throw new NotFoundHttpException('Manager not found');
                                    $obDlg->link('busers',$obManager);
                                }else{
                                    $obManagers = BUser::getManagersArr(); //иначе добавляем всех менеджеров к диалогу
                                    if(!empty($obManagers))
                                        foreach($obManagers as $obMan)
                                            $obDlg->link('busers',$obMan);
                                }
                            }else{
                                Yii::$app->session->setFlash('error',Yii::t('app/common','DIALOG_ERROR_ADD_DIALOG'));
                            }
                            $savedModels[] = $model;
                        } else {
                            $notSavedmodels[] = $model;
                        }
                    }
                }
                if($savedModels){
                    Yii::$app->session->setFlash('success', Yii::t('app/book', '{count} payments success saved', ['count'=> count($savedModels)]));
                }
                if($notSavedmodels){
                    return $this->render('migrate_form_list', [
                        'models' => $notSavedmodels,
                    ]);
                }else{
                    return $this->redirect(['index']);
                }
            }
        }
        return $this->render('migrate', [
            'model' => $model,
        ]);
    }

    protected function parseXml($xml){
        $xmlStr = file_get_contents($xml);
        $xmlStr = str_replace('<?xml:stylesheet type="text/xsl" ?>','',$xmlStr);
        $paymentsXml = simplexml_load_string($xmlStr);
        $models = [];
        foreach($paymentsXml->STATEMENTBY->CREDITDOCUMENTS->DOCUMENT as $paymentXml){
            //у основных платежей тип 1, так же платежи от физиков без UNP
            if($paymentXml->DOCUMENTTYPE==1 || $paymentXml->PAYERUNN==""){
                $existPayment = PaymentRequest::find()->andWhere(['pay_date'=>strtotime(strval($paymentXml->DOCUMENTDATE))])->andWhere(['payment_order'=> $paymentXml->DOCUMENTNUMBER.' от '. $paymentXml->DOCUMENTDATE])->all();
                if($existPayment && count($existPayment)==1){
                       continue;
                }
                $model = new PaymentRequest();
                $model->owner_id = Yii::$app->user->id;
                $model->status = PaymentRequest::STATUS_NEW;
                $model->pay_date = strval($paymentXml->DOCUMENTDATE);
                $model->pay_summ = strval($paymentXml->AMOUNT);
                $model->currency_id = ExchangeRates::getCurrencyByBankCode(intval($paymentXml->CURRCODE));
                $model->legal_id = 3;
                $model->payment_order = $paymentXml->DOCUMENTNUMBER.' от '. $paymentXml->DOCUMENTDATE;
                $model->description = strval($paymentXml->GROUND);
                if(strval($paymentXml->PAYERUNN)) {
                    $cuserRequisite = CUserRequisites::find()->where(['TRIM(ynp)' => $paymentXml->PAYERUNN . ""])->one();
                    if($cuserRequisite){
                        $model->is_unknown = 0;
                        $model->cntr_id = $cuserRequisite->id;
                        $cuser = CUser::findOneByIDCached($cuserRequisite->id);
                        $model->cuserName = $cuserRequisite->getCorpName();
                        $model->manager_id = $cuser->manager_id;
                    }else{
                        $model->is_unknown = 1;
                    }
                }else
                    $model->is_unknown = 1;

                $models[] =$model;
            }
        }
        return $models;
    }

    /**
     * Displays a single Payments model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
    /*    $model = $this->findModel($id);
        $behavior = new PaymentEnrollmentBehavior();

        $amount = $behavior->countAmoutForEnrollment($model, $model->calculate->payCond, $model->calculate);
        echo $amount;
        die;
    */
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Updates an existing Payments model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param $id
     * @return string|Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldSumm = $model->pay_summ;


        /** @var PaymentsCalculations $obCalc */
        $obCalc = $model->calculate;

        if(is_object($obCalc))
            $model->condition_id = $obCalc->pay_cond_id;
        else
            $obCalc = new PaymentsCalculations();

        if(!empty($obCalc->pay_cond_id))
        {
            $obCondTmp = $obCalc->payCond;
            if(is_object($obCondTmp) && $obCondTmp->type == PaymentCondition::TYPE_CUSTOM)
                $model->customProd = $obCalc->production;
        }
        $oldCustomProd = $model->customProd;
        if ($model->load(Yii::$app->request->post()) ) {

            $trans = Yii::$app->db->beginTransaction();

            if($model->save())
            {   //перерасчет
                if($obCalc->isNewRecord)
                {
                    /** @var PaymentCondition $obCond */
                    $obCond = PaymentCondition::findOne($model->condition_id);
                    if(empty($obCond))
                        throw new NotFoundHttpException("Condition not found");

                    $currSum = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$model->pay_date),$model->currency_id); //курс валюты в бел рублях на дату платежа

                    if(is_null($currSum))
                    {
                        $trans->rollBack();
                        throw new NotFoundHttpException('currency not found');
                    }

                    $paySumm = $model->pay_summ*$currSum;
                    $customProd = $model->customProd*$currSum;

                    $obPOp = new PaymentOperations(
                        $paySumm,$obCond->tax,$obCond->commission,$obCond->corr_factor,$obCond->sale,$obCond->type,$customProd
                    );

                    $arCount = $obPOp->getFullCalculate();

                    $obClc = new PaymentsCalculations([
                        'payment_id' => $model->id,
                        'pay_cond_id' => $obCond->id,
                        'tax' => $arCount['tax'],
                        'profit' => $arCount['profit'],
                        'production' => $arCount['production'],
                        'cnd_corr_factor' => $obCond->corr_factor,
                        'cnd_commission' => $obCond->commission,
                        'cnd_sale' => $obCond->sale,
                        'cnd_tax' => $obCond->tax,
                        'profit_for_manager' => $arCount['profit'] - ($paySumm* PaymentsCalculations::COEF_FOR_PROFIT_MANAGER),
                    ]);

                    if($obClc->save())
                    {
                        $trans->commit();
                        Yii::$app->session->setFlash('success',Yii::t('app/book',"Payment successfully updated"));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }elseif($obCalc->pay_cond_id != $model->condition_id || $model->updateWithNewCondition){

                    /** @var PaymentCondition $obCond */
                    $obCond = PaymentCondition::findOne($model->condition_id);
                    if(empty($obCond))
                        throw new NotFoundHttpException("Condition not found");

                    $currSum = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$model->pay_date),$model->currency_id); //курс валюты в бел рублях на дату платежа

                    if(is_null($currSum))
                    {
                        $trans->rollBack();
                        throw new NotFoundHttpException('currency not found');
                    }

                    $paySumm = $model->pay_summ*$currSum;
                    $customProd = $model->customProd*$currSum;

                    $obPOp = new PaymentOperations(
                        $paySumm,$obCond->tax,$obCond->commission,$obCond->corr_factor,$obCond->sale,$obCond->type,$customProd
                    );

                    $arCount = $obPOp->getFullCalculate();

                    $obCalc -> pay_cond_id = $obCond->id;
                    $obCalc -> tax = $arCount['tax'];
                    $obCalc -> profit = $arCount['profit'];
                    $obCalc -> production = $arCount['production'];
                    $obCalc -> cnd_corr_factor = $obCond->corr_factor;
                    $obCalc -> cnd_commission = $obCond->commission;
                    $obCalc -> cnd_sale = $obCond->sale;
                    $obCalc -> cnd_tax = $obCond->tax;
                    $obCalc ->profit_for_manager = $arCount['profit'] - ($paySumm* PaymentsCalculations::COEF_FOR_PROFIT_MANAGER);

                    if($obCalc->save())
                    {
                        $trans->commit();
                        Yii::$app->session->setFlash('success',Yii::t('app/book',"Payment successfully updated"));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }elseif($oldSumm != $model->pay_summ || $oldCustomProd != $model->customProd){

                    $currSum = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$model->pay_date),$model->currency_id);    //курс валюты в бел рублях на дату платежа

                    /** @var PaymentCondition $obCond */
                    $obCond = PaymentCondition::findOne($model->condition_id);
                    if(empty($obCond))
                        throw new NotFoundHttpException("Condition not found");

                    if(is_null($currSum))
                    {
                        $trans->rollBack();
                        throw new NotFoundHttpException('currency not found');
                    }

                    $paySumm = $model->pay_summ*$currSum;
                    $customProd = $model->customProd*$currSum;


                    $obPOp = new PaymentOperations(
                        $paySumm,$obCalc->cnd_tax,$obCalc->cnd_commission,$obCalc->cnd_corr_factor,$obCalc->cnd_sale,$obCond->type,$customProd
                    );

                    $arCount = $obPOp->getFullCalculate();

                    $obCalc -> tax = $arCount['tax'];
                    $obCalc -> profit = $arCount['profit'];
                    $obCalc -> production = $arCount['production'];
                    if($obCalc->save())
                    {
                        $trans->commit();
                        Yii::$app->session->setFlash('success',Yii::t('app/book',"Payment successfully updated"));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }else{
                    $trans->commit();
                    Yii::$app->session->setFlash('success',Yii::t('app/book',"Payment successfully updated"));
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
            $trans->rollBack();
            Yii::$app->session->setFlash('error',Yii::t('app/book',"Can't update payment"));
        } else {
            /** @var  CUser $obCntrID */
            $obCntrID = CUser::findOneByIDCached(is_object($cuser = $model->cuser) ? $cuser->id : 0);
            $nCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',strtotime($model->pay_date)),$model->currency_id);
            $paySumm = (float)$model->pay_summ*$nCurr;
            $arCondVisible = PaymentCondition::getAppropriateConditions(
                $model->service_id,
                $model->legal_id,
                $paySumm,
                (is_object($obCntrID) ? $obCntrID->is_resident : NULL),
                strtotime($model->pay_date));

            $arCondVisible [] = $model->condition_id;
            $arCondVisible = array_unique($arCondVisible);

            return $this->render('update', [
                'model' => $model,
                'arCondVisible' => $arCondVisible
            ]);
        }
    }

    /**
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionFindCondition()
    {
        $iServID = Yii::$app->request->post('iServID');
        $iContrID = Yii::$app->request->post('iContrID');
        $lPID = (int)Yii::$app->request->post('lPID');
        $amount = Yii::$app->request->post('amount');
        $iCurr = (int)Yii::$app->request->post('iCurr');
        $payDate = (int)Yii::$app->request->post('payDate');

        $obCntrID = CUser::findOneByIDCached($iContrID);

        if(empty($obCntrID))
            throw new NotFoundHttpException('Contractor not found');

        $nCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',strtotime($payDate)),$iCurr);
        $paySumm = (float)$amount*$nCurr;
        $arCondVisible = PaymentCondition::getAppropriateConditions(
            $iServID,
            $lPID,
            $paySumm,
            $obCntrID->is_resident,
            strtotime($payDate));

        /**
            $obPPC = CuserPreferPayCond::find()->where([    //дефолтное условие
                'cuser_id' => $obCntrID->id,
                'service_id' => $iServID
            ])->one();

            if(!empty($obPPC) && !in_array($obPPC->cond_id,$arCondVisible))
                $arCondVisible [] = $obPPC->cond_id;
        **/

        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['visable' => $arCondVisible,'default' => empty($obPPC) ? NULL : $obPPC->cond_id];
    }

    /**
     * Deletes an existing Payments model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Payments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payments::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionCreatePaymentRequest()
    {
        $model = new PaymentRequest();
        $model->owner_id = Yii::$app->user->id;
        $model->status = PaymentRequest::STATUS_NEW;
        $model->pay_date = date('d.m.Y',time());

        if($model->load(Yii::$app->request->post()))
        {
            if($model->save())
            {
                $obDlg = new Dialogs();
                $obDlg->type = Dialogs::TYPE_REQUEST;
                $obDlg->buser_id = Yii::$app->user->id;
                $obDlg->status = Dialogs::PUBLISHED;
                $obDlg->theme = Yii::t('app/book','New payment request').'<br>'.$model->description;
                if($obDlg->save())
                {
                    if(!empty($model->manager_id))//если указан менеджер, то добавляем его к диалогу
                    {
                        $obManager = BUser::findOne($model->manager_id);
                        if(empty($obManager))
                            throw new NotFoundHttpException('Manager not found');
                        $obDlg->link('busers',$obManager);
                    }else{
                        $obManagers = BUser::getManagersArr(); //иначе добавляем всех менеджеров к диалогу
                        if(!empty($obManagers))
                            foreach($obManagers as $obMan)
                                $obDlg->link('busers',$obMan);
                    }
                    Yii::$app->session->setFlash('success',Yii::t('app/common','DIALOG_SUCCESS_ADD_DIALOG'));
                }else{
                    Yii::$app->session->setFlash('error',Yii::t('app/common','DIALOG_ERROR_ADD_DIALOG'));
                }
                Yii::$app->session->setFlash('success',Yii::t('app/book','New payment request successfully added'));
                return $this->redirect(['index']);
            }
        }
        return $this->render('create_payment_request',[
            'model' => $model
        ]);
    }

    /**
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionGetManager()
    {
        $cID = Yii::$app->request->post('cID');
        /** @var CUser $obCtr */
        $obCtr = CUser::findOneByIDCached($cID);
        if(empty($obCtr))
            throw new NotFoundHttpException("Contractor ID not found");

        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['mID' => $obCtr->manager_id];
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGetConditions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $date = Yii::$app->request->post('date');
        if(!$date)
            throw new NotFoundHttpException();

        $time = strtotime($date);
        return PaymentCondition::getConditionWithCurrency(date('Y-m-d',$time));
    }
    public function actionSetSale($id, $userId){
        $behavior = new PaymentBonusBehavior();
        $model = $this->findModel($id);
        $model->isSale = true;
        $model->saleUser = $userId;
        $behavior->saveSale($model);
        $behavior->countingSimpleBonus($model);
        $behavior->countingComplexBonus($model);
        echo 'Бонус пересчитан';
        die;
    }
}
