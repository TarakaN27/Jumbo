<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\components\payment\PaymentOperations;
use common\models\CUser;

use common\models\Dialogs;
use common\models\ExchangeCurrencyHistory;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\PaymentsCalculations;

use Yii;
use common\models\Payments;
use common\models\search\PaymentsSearch;

use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

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
                    'actions' => ['index','view','create','update'],
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
        if(empty($searchModel->pay_date))
            $searchModel->pay_date = NULL;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Payments model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Payments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    /*
    public function actionCreate()
    {
        $model = new Payments();
        if(empty($model->pay_date))
            $model->pay_date = time();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $trans = Yii::$app->db->beginTransaction();
            try{
                if($model->save())
                {
                    /** @var PaymentCondition $obCond */
 /*                   $obCond = PaymentCondition::findOne($model->condition_id);
                    if(empty($obCond))
                        throw new NotFoundHttpException("Condition not found");

                    $obPOp = new PaymentOperations(
                        $model->pay_summ,$obCond->tax,$obCond->commission,$obCond->corr_factor,$obCond->sale
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
                    ]);

                    if($obClc->save())
                    {
                        $trans->commit();
                        Yii::$app->session->setFlash('success',Yii::t('app/book',"Payment successfully added"));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }else{
                        $trans->rollBack();
                        Yii::$app->session->setFlash('error',Yii::t('app/book',"Can't add payment"));
                    }
                }
            }catch (\Exception $e)
            {
                $trans->rollBack();
                Yii::$app->session->setFlash('error',Yii::t('app/book',"Can't add payment"));
            }
            $trans->rollBack();
            Yii::$app->session->setFlash('error',Yii::t('app/book',"Can't add payment"));
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
*/
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

                    $obPOp = new PaymentOperations(
                        $paySumm,$obCond->tax,$obCond->commission,$obCond->corr_factor,$obCond->sale
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

                    $obPOp = new PaymentOperations(
                        $paySumm,$obCond->tax,$obCond->commission,$obCond->corr_factor,$obCond->sale
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

                    if($obCalc->save())
                    {
                        $trans->commit();
                        Yii::$app->session->setFlash('success',Yii::t('app/book',"Payment successfully updated"));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }elseif($oldSumm != $model->pay_summ){

                    $currSum = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$model->pay_date),$model->currency_id);    //курс валюты в бел рублях на дату платежа

                    if(is_null($currSum))
                    {
                        $trans->rollBack();
                        throw new NotFoundHttpException('currency not found');
                    }

                    $paySumm = $model->pay_summ*$currSum;

                    $obPOp = new PaymentOperations(
                        $paySumm,$obCalc->cnd_tax,$obCalc->cnd_commission,$obCalc->cnd_corr_factor,$obCalc->cnd_sale
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
            return $this->render('update', [
                'model' => $model,
            ]);
        }
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
        $model->pay_date = date('Y-m-d',time());

        if($model->load(Yii::$app->request->post()) && $model->save())
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


}
