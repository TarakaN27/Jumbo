<?php

namespace backend\modules\partners\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use backend\modules\partners\models\Process1Form;
use backend\modules\partners\models\Process3Form;
use common\models\CUser;
use common\models\CuserToGroup;
use Yii;
use common\models\PartnerWithdrawalRequest;
use common\models\search\PartnerWithdrawalRequestSearch;
use yii\base\Exception;
use yii\bootstrap\Alert;
use yii\web\NotFoundHttpException;
use common\models\AbstractModel;
use yii\filters\AccessControl;
use yii\web\ServerErrorHttpException;

/**
 * PartnerWithdrawalRequestController implements the CRUD actions for PartnerWithdrawalRequest model.
 */
class PartnerWithdrawalRequestController extends AbstractBaseBackendController
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
                    'actions' => [
                        'index',
                        'view',
                        'process3'
                    ],
                    'allow' => true,
                    'roles' => ['moder']
                ],
                [
                    'allow' => true,
                    'roles' => ['admin','bookkeeper']
                ]
            ]
        ];
        return $tmp;
    }



    /**
     * Lists all PartnerWithdrawalRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PartnerWithdrawalRequestSearch();

        $addCond = NULL;
        $addParams = NULL;
        //@todo проверить
        if(Yii::$app->user->can('only_partner_manager'))
        {
            $addCond = PartnerWithdrawalRequest::tableName().'.status = :statusNew ';
            $addParams = [
                ':statusNew' => PartnerWithdrawalRequest::STATUS_NEW
            ];
        }
        //@todo проверить
        if(Yii::$app->user->can('only_manager'))
        {
            $addCond = PartnerWithdrawalRequest::tableName().'.type = :type AND '.PartnerWithdrawalRequest::tableName().'.status = :statusNew ';
            $addParams = [
                ':type' => PartnerWithdrawalRequest::TYPE_SERVICE,
                ':statusNew' => PartnerWithdrawalRequest::STATUS_MANAGER_PROCESSED
            ];
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$addCond,$addParams);

        $partnerDesc = '';
        if($searchModel->partner_id)
            $partnerDesc = CUser::getCuserInfoById($searchModel->partner_id);

        $managerDesc = '';
        if($searchModel->manager_id)
            $managerDesc = BUser::findOneByIdCachedForSelect2($searchModel->manager_id);

        $pManDesc = '';
        if($searchModel->partnerManager)
            $pManDesc = BUser::findOneByIdCachedForSelect2($searchModel->partnerManager);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'partnerDesc' => $partnerDesc,
            'managerDesc' => $managerDesc,
            'pManDesc' => $pManDesc
        ]);
    }

    /**
     * Displays a single PartnerWithdrawalRequest model.
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
     * Creates a new PartnerWithdrawalRequest model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PartnerWithdrawalRequest();
        $model->date = Yii::$app->formatter->asDate(time());
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $partnerDesc = '';
            if($model->partner_id)
                $partnerDesc = is_object($obP = CUser::find()->joinWith('requisites')->where([CUser::tableName().'.id' => $model->partner_id])->one()) ? $obP->getInfoWithSite() : NULL;

            return $this->render('create', [
                'model' => $model,
                'partnerDesc' => $partnerDesc
            ]);
        }
    }

    /**
     * Updates an existing PartnerWithdrawalRequest model.
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
     * Deletes an existing PartnerWithdrawalRequest model.
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
     * Finds the PartnerWithdrawalRequest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PartnerWithdrawalRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PartnerWithdrawalRequest::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionProcess1($id)
    {
        $model = $this->findModel($id);
        if($model->type != $model::TYPE_MONEY || $model->status != $model::STATUS_NEW)      //Shall see, what request is correct
            throw new Exception($message = "Illegal request", $code = 404);

        $models = [new Process1Form([
            'obRequest' => $model,
            'amount' => $model->amount
        ])];

        $obBookkeeper = BUser::getBookkeeperForPartnerWithdrawal();             //Find bookkeeper for withdrawal request

        if(Yii::$app->request->post('Process1Form'))
        {
            $models = AbstractModel::createMultiple(Process1Form::classname());
            /** @var Process1Form $mod */
            foreach ($models as &$mod) {
                $mod->obRequest = $model;
                $mod->obBookkeeper = $obBookkeeper;
            }
            AbstractModel::loadMultiple($models,Yii::$app->request->post());
            $valid = AbstractModel::validateMultiple($models);
            
            $amount = 0;                                                        //Check, what all amount is spent
            foreach ($models as $item)
            {
                $amount+=(float)$item->amount;
            }
            
            if($amount != $model->amount)
            {
                $valid = false;
                /** @var Process1Form $item */
                foreach ($models as &$item)
                    $item->addError('amount',Yii::t('app/users','Invalid amount'));
            }

            if($valid)
            {
                $transaction = \Yii::$app->db->beginTransaction();              //Begin transaction, because we works with few models
                try {
                    $bFlag = TRUE;                                              //Set share save flag
                    /** @var Process1Form $item */
                    foreach ($models as $item)
                        if(!$item->makeRequest())                               //If save model is unsuccessful
                        {
                            $bFlag = FALSE;
                            break;
                        }
                    if($bFlag)                                                  //All good, apply transaction
                    {
                        $model->status = PartnerWithdrawalRequest::STATUS_DONE;
                        if(!$model->save())
                            throw new ServerErrorHttpException();

                        $transaction->commit();
                        Yii::$app->session->setFlash(\backend\widgets\Alert::TYPE_SUCCESS,Yii::t('app/users','Request successfully processed'));
                        return $this->redirect(['index']);
                    }else{
                        $transaction->rollBack();
                    }
                }catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }
        
        $arContractor = CuserToGroup::getUserByGroup($model->partner_id);       //Get contractors by partner id

        return $this->render('process1',[
            'model' => $model,
            'arContractor' => $arContractor,
            'models' => $models,
            'obBookkeeper' => $obBookkeeper
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionProcess2($id)
    {
        $model = $this->findModel($id);
        if($model->type != $model::TYPE_SERVICE || $model->status != $model::STATUS_NEW)        //Shall see, what request is correct
            throw new Exception($message = "Illegal request", $code = 404);

        $model->setScenario(PartnerWithdrawalRequest::SCENARIO_SET_MANAGER);                    //Set scenario for apply validate rules

        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->status = PartnerWithdrawalRequest::STATUS_MANAGER_PROCESSED;
            if($model->save())
            {
                Yii::$app->session->setFlash(\backend\widgets\Alert::TYPE_SUCCESS,Yii::t('app/users','Request successfully processed'));
                return $this->redirect(['index']);
            }
        }
        $manDesc = '';
        if($model->manager_id)
            $manDesc = BUser::findOneByIdCachedForSelect2($model->manager_id);

        return $this->render('process2',[
            'model' => $model,
            'manDesc' => $manDesc
        ]);
    }

    public function actionProcess3($id)
    {
        $model = $this->findModel($id);
        if($model->type != $model::TYPE_SERVICE || $model->status != $model::STATUS_MANAGER_PROCESSED)        //Shall see, what request is correct
            throw new Exception($message = "Illegal request", $code = 404);
        
        $arModels = [new Process3Form(['amount' => $model->amount])];


        if(Yii::$app->request->post('Process3Form'))
        {
            $arModels = AbstractModel::createMultiple(Process3Form::classname());
            /** @var Process1Form $mod */
            foreach ($arModels as &$mod) {
                $mod->obRequest = $model;
            }
            AbstractModel::loadMultiple($arModels,Yii::$app->request->post());
            $valid = AbstractModel::validateMultiple($arModels);

            $amount = 0;                                                        //Check, what all amount is spent
            foreach ($arModels as $item)
            {
                $amount+=(float)$item->amount;
            }

            if($amount != $model->amount)
            {
                $valid = false;
                /** @var Process3Form $item */
                foreach ($arModels as &$item)
                    $item->addError('amount',Yii::t('app/users','Invalid amount'));
            }

            if($valid)
            {
                $transaction = \Yii::$app->db->beginTransaction();              //Begin transaction, because we works with few models
                try {
                    $bFlag = TRUE;                                              //Set share save flag
                    /** @var Process3Form $item */
                    foreach ($arModels as $item)
                        if(!$item->makeRequest())                               //If save model is unsuccessful
                        {
                            $bFlag = FALSE;
                            break;
                        }
                    if($bFlag)                                                  //All good, apply transaction
                    {

                        $model->status = PartnerWithdrawalRequest::STATUS_DONE;
                        if(!$model->save())
                            throw new ServerErrorHttpException();

                        $transaction->commit();
                        Yii::$app->session->setFlash(\backend\widgets\Alert::TYPE_SUCCESS,Yii::t('app/users','Request successfully processed'));
                        return $this->redirect(['index']);
                    }else{
                        $transaction->rollBack();
                    }
                }catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        $arContractor = CuserToGroup::getUserByGroup($model->partner_id);       //Get contractors by partner id

        return $this->render('process3',[
            'model' => $model,
            'arModels' => $arModels,
            'arContractor' => $arContractor
        ]);
    }
}
