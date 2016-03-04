<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use backend\modules\bookkeeping\form\EnrollProcessForm;
use backend\widgets\Alert;
use common\models\CUser;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\PaymentCondition;
use common\models\PaymentsCalculations;
use common\models\PromisedPayment;
use Yii;
use common\models\EnrollmentRequest;
use common\models\search\EnrollmentRequestSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

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
        if(!Yii::$app->user->can('adminRights'))    //показываем админам все запросы, другим только свои
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
        if($model->status == EnrollmentRequest::STATUS_PROCESSED)
        {
            Yii::$app->session->setFlash('error',Yii::t('app/book','Request already processed'));
        }
        $model->callViewedEvent();


        $obForm = new EnrollProcessForm();
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
            $arPromised = PromisedPayment::find()->where([
                'cuser_id' => $model->cuser_id,
                'service_id' => $model->service_id
            ])->andWhere('(paid is NULL OR paid = 0)')->all();
            $obPayment = $model->payment;
            $obForm->arPromised = $arPromised;

            if(!empty($arPromised))
            {
                foreach($arPromised as $pro)
                    $countPromised+=$pro->amount;
            }

            if(!empty($countPromised))
            {
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

        if($obForm->load(Yii::$app->request->post()))
        {
            if($obForm->makeRequest())
            {
                Yii::$app->session->setFlash(Alert::TYPE_SUCCESS,Yii::t('app/book','Enrollment request successfully processed'));
                return $this->redirect(['/bookkeeping/enrolls/index']);
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
            'exchRate' => $exchRate
        ]);

    }
}
