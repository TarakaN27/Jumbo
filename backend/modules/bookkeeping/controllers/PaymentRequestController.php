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
use common\components\payment\PaymentOperations;
use common\models\AbstractModel;
use common\models\CUser;
use common\models\CUserRequisites;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\Payments;
use common\models\PaymentsCalculations;
use common\models\search\PaymentRequestSearch;
use Yii;
use yii\base\Exception;
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
               // [
               //     'actions' => ['index','view','create','update'],
               //     'allow' => true,
               //     'roles' => ['moder']
               // ],
                [
                    'allow' => true,
                    'roles' => ['admin','bookkeeper','moder']
                ]
            ]
        ];


        return $tmp;
    }


    public function actionIndex()
    {
        $searchModel = new PaymentRequestSearch();
        if(Yii::$app->user->can('only_manager'))
            $searchModel->managerID = Yii::$app->user->id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if(empty($searchModel->pay_date))
            $searchModel->pay_date = NULL;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAddPayment($pID)
    {
        $modelP = PaymentRequest::findOne($pID);
        if(empty($modelP))
            throw new NotFoundHttpException('Payment request not found');

        if($modelP->manager_id != Yii::$app->user->id)
            throw new ForbiddenHttpException('You are not allowed to perform this action');

        if(!Yii::$app->request->post('AddPaymentForm'))
            $model = [new AddPaymentForm(['fullSumm' => $modelP->pay_summ])];
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

            if($tmpSumm != $modelP->pay_summ)
                Yii::$app->session->setFlash('error',Yii::t('app/book','You have to spend all amout'));
            else
                $validSumm = TRUE;

            if($valid &&  $validSumm)
            {
                $transaction = \Yii::$app->db->beginTransaction();
                try {

                        $bError = FALSE;
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
                                'prequest_id' => $modelP->id

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

                            $obOp = new PaymentOperations($p->summ,$obCond->tax,$obCond->commission,$obCond->corr_factor,$obCond->sale);
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
                            ]);

                            if(!$obPayCalc->save())
                            {
                                $bError = TRUE;
                                break;
                            }

                            unset($obPay,$obPayCalc,$obCond,$obOp);
                        }

                        if(!$bError)
                        {
                            $modelP->status = PaymentRequest::STATUS_FINISHED;
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
            'modelP' => $modelP
        ]);
    }

    /**
     * @param $pID
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionPinPaymentToManager($pID)
    {
        $modelP = PaymentRequest::find()
            ->where(['id' => $pID])
            ->one();
        if(empty($modelP))
            throw new NotFoundHttpException('Payment request not found');

        if(!empty($modelP->cntr_id))
        {
            $obCUser = CUser::findOne($modelP->cntr_id);
            if(empty($obCUser))
                throw new NotFoundHttpException('Contractor not found');
            if($obCUser->manager_id == Yii::$app->user->id)
            {
                $modelP->manager_id = Yii::$app->user->id;
                if($modelP->save())
                    Yii::$app->session->setFlash('success',Yii::t('app/book','Payment successfully pined'));
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
            $arContrMap[$ac->id] = $ac->getInfo();
        }

        $model = new SetManagerContractorForm(['obPR' => $modelP,'contractorMap' => $arContrMap]);
        if($model->load(Yii::$app->request->post()) && $model->makeRequest())
        {
            Yii::$app->session->setFlash('success',Yii::t('app/book','Payment successfully pined'));
            return $this->redirect(['index']);
        }
        return $this->render('pin_payment_to_manager',[
            'model' => $model,
            'arContrMap' => $arContrMap
        ]);
    }

    public function actionView($id)
    {
        $model = PaymentRequest::find()
            ->where(['id' => $id])
            ->with('owner','legal','currency','cuser')
            ->one();

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

        $obCntrID = CUser::findOneByIDCached($iContrID);

        if(empty($obCntrID))
            throw new NotFoundHttpException('Contractor not found');

        $obCond = PaymentCondition::find()
            ->select('id')
            ->where([
                'service_id' => (int)$iServID,
                'l_person_id' => (int)$lPID,
                'is_resident' => (int)$obCntrID->is_resident
            ])
            ->orderBy('id DESC')
            ->one();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['cID' => empty($obCond) ? FALSE : $obCond->id];
    }

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

} 