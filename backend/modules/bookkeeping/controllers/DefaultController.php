<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\components\payment\PaymentBonusBehavior;
use common\components\payment\PaymentEnrollmentBehavior;
use common\components\payment\PaymentOperations;
use common\models\Acts;
use common\models\ActToPayments;
use common\models\BankDetails;
use common\models\CUser;

use common\models\CuserBankDetails;
use common\models\CuserPreferPayCond;
use common\models\CUserRequisites;
use common\models\Dialogs;
use common\models\EnrollmentRequest;
use common\models\Enrolls;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\LegalPerson;
use common\models\PartnerPurseHistory;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\PaymentsCalculations;

use common\models\PromisedPayment;
use common\models\PromisedPayRepay;
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
                    'allow' => false,
                    'roles' => ['teamlead']
                ],
                [
                    'actions' => ['index','view','create','update','get-conditions','find-condition'],
                    'allow' => true,
                    'roles' => ['moder','sale']
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
                $models = $this->parseFile($_FILES['MigrateLoadFileForm']['tmp_name']['src']);
                if($models) {
					var_dump($models[0]);
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
					
					$request = PaymentRequest::find()->where([
						'cntr_id'=>$model->cntr_id,
						'pay_summ'=>$model->pay_summ,
						'status'=>$model->status,
						'currency_id'=>$model->currency_id,
						'payment_order'=>$model->payment_order,
						'legal_id'=>$model->legal_id,
						'service_id'=>$model->service_id,
						'bank_id'=>$model->bank_id,
						'payed'=>0
					])->one();
					
					#Если существует то заменяем данные, если новый то создаем
					
					if(!empty($request)){
						$request->owner_id = $model->owner_id;
						$request->status = $model->status;				
						$request->description = $model->description;				
						$request->pay_date = $model->pay_date;	
						$request->payed = 1;		
						$model = $request;
					}		
					
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
    protected function parseMTBank($paymentsXml){
        $models = [];
        foreach($paymentsXml->QUERY->OUTPUT->DOC as $paymentXml){
            //у основных платежей тип 1, так же платежи от физиков без UNP
            if($paymentXml['Credit']>0 && ($paymentXml->VidDoc=='01' || $paymentXml->UNNRec=="")){
                $existPayment = PaymentRequest::find()->andWhere(['bank_id'=>1, 'pay_date'=>strtotime(strval($paymentXml['DocDate']))])->andWhere(['payment_order'=> strval($paymentXml['Num']).' от '. $paymentXml['DocDate']])->andWhere(['!=', 'status', '5'])->all();
                if($existPayment && count($existPayment)==1){
                    continue;
                }
                $model = new PaymentRequest();
                $model->owner_id = Yii::$app->user->id;
                $model->status = PaymentRequest::STATUS_NEW;
                $model->pay_date = strval($paymentXml['DocDate']);
                $model->pay_summ = strval($paymentXml['Credit']);
                $model->bank_id = 1;
                $model->currency_id = ExchangeRates::getCurrencyByBankCode(intval(933));
                $model->legal_id = 3;
                $model->payment_order = $paymentXml['Num'].' от '.  $paymentXml['DocDate'];
                $model->description = strval($paymentXml->Nazn);
                if(strval($paymentXml->UNNRec)) {
                    $cuserRequisite = CUserRequisites::find()->where(['TRIM(ynp)' => $paymentXml->UNNRec . ""])->one();
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
    protected function parseAlfaBank($file){
        $lines = file($file);
        $payments=[];
        $paymentCounter = 0;
        $isFinish = false;
        foreach($lines as $key=>$line){
            $lines[$key] = $line = trim(str_replace("^", "",$line));
            if(strpos($line, "Header4=")!== false){
                $account = str_replace("Header4=", "", $line);
            }
            if(strpos($line, "DocDate")!== false){
                ++$paymentCounter;
            }
            if(strpos($line, "DB")!==false){
                $isFinish = true;
            }
            if($paymentCounter && !$isFinish){
                $line = explode("=", $line);
                if(count($line) >= 2) {
                    $key = array_shift($line);
                    $payments[$paymentCounter][$key] = implode('=',$line);
                }
            }
        }
        if($account) {
            $bankDetail = BankDetails::find()->where(['LIKE', 'bank_details', $account])->one();
        }

        $models = [];

        foreach($payments as $payment){
            //у основных платежей тип 1, так же платежи от физиков без UNP
            if($payment['Credit']>0){
                $existPayment = PaymentRequest::find()->andWhere(['bank_id'=>$bankDetail->id, 'pay_date'=>strtotime(strval($payment['DocDate']))])->andWhere(['payment_order'=> strval($payment['Num']).' от '. $payment['DocDate']])->andWhere(['!=', 'status', '5'])->all();
                if($existPayment && count($existPayment)==1){
                    continue;
                }
                $model = new PaymentRequest();
                $model->owner_id = Yii::$app->user->id;
                $model->status = PaymentRequest::STATUS_NEW;
                $model->pay_date = strval($payment['DocDate']);
                $model->pay_summ = strval($payment['Credit']);
                $model->bank_id = $bankDetail->id;
                $model->currency_id = ExchangeRates::getCurrencyByBankCode(intval(933));
                $model->legal_id = $bankDetail->legal_person_id;
                $model->payment_order = $payment['Num'].' от '.  $payment['DocDate'];
                if(!isset($payment['Nazn'])){
                    var_dump($payment);
                    die;
                }
                $model->description = iconv("windows-1251", "UTF-8", strval($payment['Nazn'].$payment['Nazn2']));
                if(strval($payment['KorUNP'])) {
                    $cuserRequisite = CUserRequisites::find()->where(['TRIM(ynp)' => $payment['KorUNP'] . ""])->one();
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

    protected function parseBLRBank($paymentsXml){
        $models = [];
        $date = str_replace("за ", "", $paymentsXml->ACCOUNTINFO->PERIOD);
        foreach($paymentsXml->ACCOUNTINFO->OPERINFO->OPER as $paymentXml){
            //у основных платежей тип 1, так же платежи от физиков без UNP
            $sum = floatval($paymentXml->SUMOPER['ek']);
            if($sum>0) {
                $existPayment = PaymentRequest::find()->andWhere(['bank_id'=>3,'pay_date'=>strtotime($date)])->andWhere(['payment_order'=> strval($paymentXml->DOCN).' от '. $date])->andWhere(['!=', 'status', '5'])->all();
                if($existPayment && count($existPayment)==1){
                    continue;
                }
                $model = new PaymentRequest();
                $model->owner_id = Yii::$app->user->id;
                $model->status = PaymentRequest::STATUS_NEW;
                $model->pay_date = $date;
                $model->pay_summ = $sum;
                $model->bank_id = 3;
                $model->currency_id = ExchangeRates::getCurrencyByBankCode(intval($paymentXml->RATEINFO['Code']));
                $model->legal_id = 3;
                $model->payment_order = $paymentXml->DOCN.' от '.  $date;
                $model->description = strval($paymentXml->DETPAY);
                if(strval($paymentXml->UNPKORR)) {
                    $cuserRequisite = CUserRequisites::find()->where(['TRIM(ynp)' => $paymentXml->UNPKORR . ""])->one();
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

    protected function parseFile($file){
        $fileContent = file_get_contents($file);
        if(strpos($fileContent, '[OUT_PARAM]')){
            $models = $this->parseAlfaBank($file);
        }else{
            $paymentsXml = simplexml_load_string($fileContent);
            $models = [];
            if (isset($paymentsXml->QUERY)) {
                $models = $this->parseMTBank($paymentsXml);
            } elseif (isset($paymentsXml->ACCOUNTINFO)) {
                $models = $this->parseBLRBank($paymentsXml);
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
        //return $this->redirect(['index']);
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
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $approve = Yii::$app->request->post('approve');

        switch($approve){
            case 'true':
                $paymentRequestId = $this->findModel($id)->prequest_id;
                $result = ['approve'=>'error'];
                if($paymentRequestId){
                    $trans = Yii::$app->db->beginTransaction();
                    try{
                        $this->deleteEnrollByPaymentRequestId($paymentRequestId);
                        $this->deletePaymentsByPaymentRequestId($paymentRequestId);
                        $this->changePaymentRequestStatus($paymentRequestId);

                        $trans->commit();

                        $result = ['approve'=>'done'];
                    }catch (\Exception $e){
                        $trans->rollBack();
                        throw $e;
                    }
                }
                return json_encode($result);
                break;
            case 'false':
                $result = [];
                $paymentsRows = Payments::find()
                    ->select(Payments::tableName().'.id as pay_id, table3.id as enr_req_id, table4.id as enroll_id, table5.id as prom_pay_rep_id, table5.pr_pay_id, table6.id as pur_hist')
                    ->leftJoin(Payments::tableName().'as table2 ',Payments::tableName().'.prequest_id = table2.prequest_id ')
                    ->leftJoin(EnrollmentRequest::tableName().'as table3 ',Payments::tableName().'.id = table3.payment_id')
                    ->leftJoin(Enrolls::tableName().'as table4 ','table3.id = table4.enr_req_id')
                    ->leftJoin(PromisedPayRepay::tableName().'as table5 ',Payments::tableName().'.id = table5.payment_id and table4.id = table5.enroll_id')
                    ->leftJoin(PartnerPurseHistory::tableName().'as table6 ',Payments::tableName().'.id = table6.payment_id')
                    ->where(['table2.id'=>$id])
                    ->asArray()
                    ->all();

                $i = 0;
                foreach ($paymentsRows as $row){
                    $result['payments'][$i]['id'] = $row['pay_id'];
                    $result['payments'][$i]['url'] = Yii::$app->getUrlManager()->createUrl(['bookkeeping/default/view','id'=>$row['pay_id']]);

                    if(isset($row['enrolls_req'])) {
                        $result['enrolls_req'][$i]['id'] = $row['enr_req_id'];
                        $result['enrolls_req'][$i]['url'] = Yii::$app->getUrlManager()->createUrl(['bookkeeping/enrollment-request/process', 'id' => $row['enr_req_id']]);
                    }
                    if(isset($row['enroll_id'])){
                        $result['enrolls'][$i]['id'] = $row['enroll_id'];
                        $result['enrolls'][$i]['url'] = Yii::$app->getUrlManager()->createUrl(['bookkeeping/enrolls/view','id'=>$row['enroll_id']]);
                    }

                    if(isset($row['prom_pay_rep_id'])){
                        $result['prom_pays'][$i]['id'] = $row['prom_pay_rep_id'];
                        $result['prom_pays'][$i]['url'] = Yii::$app->getUrlManager()->createUrl(['bookkeeping/promised-payment/view','id'=>$row['pr_pay_id']]);
                    }

                    if(isset($row['pur_hist'])){
                        $result['pur_hist'][$i]['pur_hist'] = true;
                    }


                    $i++;
                }

                return json_encode($this->renderPartial('_del_pay_records', [
                    'result' => $result,
                ]));
                break;
            case 'done':
                return $this->redirect(['index']);
                break;
            default:
                return $this->redirect(['index']);
                break;
        }
    }

    protected function deleteEnrollByPaymentRequestId($id){
        $enrolls = Enrolls::find()
            ->leftJoin(EnrollmentRequest::tableName().'as table2','table2.id = '.Enrolls::tableName().'.enr_req_id')
            ->leftJoin(Payments::tableName().'as table3',' table3.id = table2.payment_id')
            ->leftJoin(PaymentRequest::tableName().'as table4', ' table4.id = table3.prequest_id')
            ->where(['table4.id'=> $id])
            ->all();

        foreach ($enrolls as $enroll){
            $enroll->delete();
        }
    }

    protected function deletePaymentsByPaymentRequestId($id){
        $paymentsRow = Payments::find()->where(['prequest_id'=>$id])->all();

        foreach ($paymentsRow as $payment){
            $payment->delete();
        }
    }

    protected function changePaymentRequestStatus($id){
        $request = PaymentRequest::find()->where(['id' => $id])->one();

        $request->status = 5;
        $request->update();
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
            $model->bank_id = isset($model->bank[$model->legal_id])?$model->bank[$model->legal_id]:null;
			
			$request = PaymentRequest::find()->where([
				'cntr_id'=>$model->cntr_id,
				'pay_summ'=>$model->pay_summ,
				'status'=>$model->status,
				'currency_id'=>$model->currency_id,
				'payment_order'=>$model->payment_order,
				'legal_id'=>$model->legal_id,
				'service_id'=>$model->service_id,
				'bank_id'=>$model->bank_id,
				'payed'=>0
			])->one();
			
			#Если существует то заменяем данные, если новый то создаем
			
			if(!empty($request)){
				$request->owner_id = $model->owner_id;
				$request->status = $model->status;
				$request->pay_date = $model->pay_date;
				$request->bank_id = $model->bank_id;				
				$request->payed = 1;				
				$model = $request;
			}
			
			
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
            'model' => $model,
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

        $bankIds = CuserBankDetails::findAll(['cuser_id'=>$cID]);
        if($bankIds){
            $bankIds = ArrayHelper::map($bankIds,'legal_person_id','bank_details_id');
        }else{
            $bankIds = LegalPerson::getLegalPersonForBill();
            $bankIds = ArrayHelper::map($bankIds, 'id','default_bank_id');
        }
        return ['mID' => $obCtr->manager_id, 'banks'=>$bankIds];
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
