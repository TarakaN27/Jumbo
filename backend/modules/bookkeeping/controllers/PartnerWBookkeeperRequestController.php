<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\managers\PartnerWBookkeeperRequestManager;
use Yii;
use common\models\PartnerWBookkeeperRequest;
use common\models\search\PartnerWBookkeeperRequestSearch;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use common\models\CuserToGroup;
use yii\filters\AccessControl;


/**
 * PartnerWBookkeeperRequestController implements the CRUD actions for PartnerWBookkeeperRequest model.
 */
class PartnerWBookkeeperRequestController extends AbstractBaseBackendController
{
    public function behaviors()
    {
        $tmp = parent::behaviors();
        $tmp['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['admin','bookkeeper']
                ]
            ]
        ];
        return $tmp;
    }
    /**
     * Lists all PartnerWBookkeeperRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PartnerWBookkeeperRequestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PartnerWBookkeeperRequest model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model ,
        ]);
    }

    /**
     * Creates a new PartnerWBookkeeperRequest model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PartnerWBookkeeperRequest();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PartnerWBookkeeperRequest model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    /*
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
    */

    /**
     * Deletes an existing PartnerWBookkeeperRequest model.
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
     * Finds the PartnerWBookkeeperRequest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PartnerWBookkeeperRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PartnerWBookkeeperRequest::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionProcess($id)
    {
        /** @var PartnerWBookkeeperRequestManager $model */
        $model = PartnerWBookkeeperRequestManager::find()
            ->with('buser','partner','contractor','currency','legal')
            ->where(['id' => $id])
            ->one();
        $model->setScenario('update');
        if(!$model)
            throw new NotFoundHttpException('Request not found');

        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->bank && isset($model->bank[$model->legal_id])) {
                $model->bank_id = $model->bank[$model->legal_id];
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->status = PartnerWBookkeeperRequest::STATUS_DONE;
                if(!$model->save())
                    throw new ServerErrorHttpException();

                if(!$model->processPartnerWithdrawal())
                    throw new ServerErrorHttpException();
                
                $transaction->commit();
                return $this->redirect(['index']);
            }catch (Exception $e)
            {
                $transaction->rollBack();
            }
        }
        $arContractor = CuserToGroup::getUserByGroup($model->partner_id);
        return $this->render('process',[
            'model' => $model,
            'arContractor'=>$arContractor,
        ]);
    }
    public function actionPdf($id)
    {
        $model = PartnerWBookkeeperRequestManager::find()->where(['id' => $id])->one();
    }

}
