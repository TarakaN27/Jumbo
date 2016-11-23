<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use backend\modules\bookkeeping\form\EnrollProcessForm;
use backend\widgets\Alert;
use common\components\payment\PaymentEnrollmentBehavior;
use common\models\CUser;
use common\models\CuserToGroup;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\PaymentsCalculations;
use common\models\PromisedPayment;
use common\models\PromisedPayRepay;
use common\models\Services;
use Yii;
use common\models\EnrollmentRequest;
use common\models\search\EnrollmentRequestSearch;
use yii\base\InvalidParamException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * EnrollmentRequestController implements the CRUD actions for EnrollmentRequest model.
 */
class EnrollmentRequestController extends AbstractBaseBackendController
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
                    'allow' => true,
                    'roles' => ['admin','bookkeeper','moder']
                ]
            ]
        ];
        return $tmp;
    }

    /**
     * Lists all EnrollmentRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EnrollmentRequestSearch();

        $additionQuery = [];
        if(!Yii::$app->user->can('adminRights') && !Yii::$app->user->can('only_bookkeeper'))    //показываем админам все запросы, другим только свои
            $additionQuery = ['assigned_id' => Yii::$app->user->id,EnrollmentRequest::tableName().'.status' => EnrollmentRequest::STATUS_NEW];
        else
            $additionQuery = [EnrollmentRequest::tableName().'.status' => EnrollmentRequest::STATUS_NEW];

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$additionQuery);
        $arTotal = $searchModel->countTotal(Yii::$app->request->queryParams,$additionQuery);

        $cuserDesc = '';
        if(!empty($searchModel->cuser_id))
        {
            $obUser = CUser::findOne($searchModel->cuser_id);
            if($obUser)
                $cuserDesc = $obUser->getInfo();
        }

        $buserDesc = '';
        if(!empty($searchModel->assigned_id))
        {
            $obBUser = BUser::findOne($searchModel->assigned_id);
            if($obBUser)
                $buserDesc = $obBUser->getFio();
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cuserDesc' => $cuserDesc,
            'buserDesc' =>  $buserDesc,
            'arTotal' => $arTotal
        ]);
    }

    /**
     * Displays a single EnrollmentRequest model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model =  $this->findModel($id);
        $model->callViewedEvent();
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new EnrollmentRequest model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new EnrollmentRequest();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing EnrollmentRequest model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing EnrollmentRequest model.
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
     * Finds the EnrollmentRequest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return EnrollmentRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EnrollmentRequest::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionProcess($id)
    {
        $model = $this->findModel($id);
        $model->callViewedEvent();              // call viewed event
        if($model->status == EnrollmentRequest::STATUS_PROCESSED)       //check if request already processed
        {
            Yii::$app->session->setFlash('error',Yii::t('app/book','Request already processed'));
            return $this->redirect(['index']);
        }
        if(Yii::$app->user->can('only_bookkeeper') && $model->assigned_id != Yii::$app->user->id)   //if user is bookkeeper , check that he has rights for processed
        {
            throw new ForbiddenHttpException();
        }
        $obForm = new EnrollProcessForm();          //create enroll form model
        $obForm->request = $model;
        $obForm->availableAmount = $model->amount;

        $obPrPay = NULL;
        $obCalc = NULL;
        $obCurr = NULL;
        $obCond = NULL;
        $obPayment = NULL;
        $arPromised = [];
        $countPromised = NULL;
        $exchRate = NULL;
        $dubExchRate = NULL;
        if(!empty($model->pr_payment_id))
        {
            $obPrPay = $model->prPayment;
            if(!$obPrPay)
                throw new NotFoundHttpException('Promised payment not found');

            $obForm->availableAmount = $model->amount;
            $obForm->enroll = $model->amount;

        }else{
            $obForm->isPayment = true;
            $obCalc = PaymentsCalculations::find()->where(['payment_id' => $model->payment_id])->one();
            $obCurr = ExchangeRates::findOne($model->pay_currency);

            /** @var PaymentCondition $obCond */
            $obCond = is_object($obCalc) ? $obCalc->payCond : NULL;
            
            
            $arUserGroup = CuserToGroup::getAllUserIdsAtGroupByUserId($model->cuser_id);
            
            $arPromised = PromisedPayment::find()
                ->select([
                    PromisedPayment::tableName().'.id',
                    PromisedPayment::tableName().'.cuser_id',
                    PromisedPayment::tableName().'.amount',
                    PromisedPayment::tableName().'.description',
                    'owner',
                    'service_id',
                    BUser::tableName().'.fname',
                    Buser::tableName().'.mname',
                    BUser::tableName().'.lname',
                    Services::tableName().'.name'
                ])
                ->joinWith('addedBy')
                ->joinWith('service')
                ->where([
                    'cuser_id' => $arUserGroup,
                    'service_id' => $model->service_id
                ])
                ->andWhere('(paid is NULL OR paid = 0)')
                ->all();

            $arPPIDs = [];
            foreach ($arPromised as $pr)
                $arPPIDs [] = $pr->id;

            $sumPPRepay = (float)PromisedPayRepay::find()->where(['pr_pay_id' => $arPPIDs])->sum('amount');

            $obPayment = $model->payment;
            $obForm->arPromised = $arPromised;

            if(!empty($arPromised))
            {
                foreach($arPromised as $pro)
                    $countPromised+=$pro->amount;
            }

            if(!empty($countPromised))
            {
                $countPromised = $countPromised-$sumPPRepay;
                if($countPromised >= $model->amount)
                {
                    $obForm->enroll = 0;
                    $obForm->repay = $model->amount;
                }else{
                    $obForm->repay = $countPromised;
                    $obForm->enroll = $model->amount - $countPromised;
                }
            }else{
                $obForm->enroll = $model->amount;
            }

            if(!empty($obCond) && !empty($obPayment))
            {
                $exchRate = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$obPayment->pay_date),$obCond->cond_currency);
            }
        }

        if($obForm->load(Yii::$app->request->post()) && $obForm->validate())
        {
            if($obForm->makeRequest())
            {
                Yii::$app->session->setFlash(Alert::TYPE_SUCCESS,Yii::t('app/book','Enrollment request successfully processed'));
                return $this->redirect(['/bookkeeping/enrolls/index']);
            }
        }

        $cuserDesc = '';
        if(!empty($obForm->cuserOP))
        {
            /** @var CUser $obCuser */
            $obCuser = CUser::find()->where(['id' => $obForm->cuserOP])->joinWith('requisites')->one();
            $cuserDesc = $obCuser->getInfoWithSite();
        }
        $dubExchRate = NULL;
        if($obCond->is_dub_currency) {
            $enrollBehavior = new PaymentEnrollmentBehavior();
            $model->dubAmount = $enrollBehavior->countAmoutForEnrollment($obPayment, $obCond, $obCalc, true);
            if(!empty($obCond) && !empty($obPayment))
            {
                $dubExchRate = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$obPayment->pay_date),$obCond->dub_cond_currency);
            }
        }
        return $this->render('process',[
            'model' => $model,
            'obPrPay' => $obPrPay,
            'obCalc' => $obCalc,
            'obCurr' => $obCurr,
            'obCond' => $obCond,
            'obForm' => $obForm,
            'arPromised' => $arPromised,
            'obPayment' => $obPayment,
            'exchRate' => $exchRate,
            'dubExchRate' =>$dubExchRate,
            'cuserDesc' => $cuserDesc
        ]);

    }

    /**
     * @return array
     */
    public function actionGetPromisedPayment()
    {
        $cID = Yii::$app->request->post('cuserID');
        $cIPOP = Yii::$app->request->post('cuserOP');
        $servID = Yii::$app->request->post('servID');

        if(empty($servID))
            throw new InvalidParamException();

        $arUserID = [];
        $arGroupUsers = [];
        if(!empty($cID)) {
            //$arUserID [] = (int)$cID;
            $arGroupUsers = $arUserID = CuserToGroup::getAllUserIdsAtGroupByUserId((int)$cID);
        }

        $orderType = SORT_ASC;
        if(!empty($cIPOP))
        {
            $arUserID [] = (int)$cIPOP;
            if($cIPOP < $cID)
                $orderType = SORT_DESC;
        }


        if(empty($arUserID))
            throw new InvalidParamException();

        $arPromised = PromisedPayment::find()
            ->select([
                PromisedPayment::tableName().'.id',
                PromisedPayment::tableName().'.cuser_id',
                PromisedPayment::tableName().'.amount',
                PromisedPayment::tableName().'.description',
                'owner',
                'service_id',
                BUser::tableName().'.fname',
                Buser::tableName().'.mname',
                BUser::tableName().'.lname',
                Services::tableName().'.name'
            ])
            ->joinWith('addedBy')
            ->joinWith('service')
            ->where([
                'cuser_id' => $arUserID,
                'service_id' => $servID
            ])
            ->andWhere('(paid is NULL OR paid = 0)');

        if(!empty($arGroupUsers))
        {
            $arPromised->orderBy(['FIELD(cuser_id,'.implode(',',$arGroupUsers).')' => SORT_DESC]);      //вначе идут ОП контрагента и его группы, затем чужие
        }else{
            $arPromised->orderBy(['cuser_id' => $orderType]);                   //нет группы
        }
            //$arPromised = $arPromised->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
        $arPromised = $arPromised->all();

        $amount = 0;
        foreach($arPromised as $pr)
        {
            $repay = $pr->repay;
            $repAmount = 0;
            foreach($repay as $rep)
                $repAmount+=$rep->amount;

            $amount+=($pr->amount-$repAmount);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'grid' => $this->renderPartial('_promised_grid',[
                'arPromised' => $arPromised
                ]),
            'amount' => $amount
        ];
    }

}
