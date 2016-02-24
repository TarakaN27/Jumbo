<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use backend\modules\bookkeeping\form\EnrollProcessForm;
use common\models\CUser;
use Yii;
use common\models\EnrollmentRequest;
use common\models\search\EnrollmentRequestSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * EnrollmentRequestController implements the CRUD actions for EnrollmentRequest model.
 */
class EnrollmentRequestController extends AbstractBaseBackendController
{
    /**
     * Lists all EnrollmentRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EnrollmentRequestSearch();

        $additionQuery = [];
        if(!Yii::$app->user->can('adminRights'))    //показываем админам все запросы, другим только свои
            $additionQuery = ['assigned_id' => Yii::$app->user->id];

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$additionQuery);

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
            'buserDesc' =>  $buserDesc
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
        $obForm = new EnrollProcessForm();
        $obForm->request = $model;

        $obPrPay = NULL;
        if(!empty($model->pr_payment_id))
        {
            $obPrPay = $model->prPayment;
            if(!$obPrPay)
                throw new NotFoundHttpException('Promised payment not found');

        }else{
            $obForm->isPayment = true;
        }



        return $this->render('process',[
            'model' => $model,
            'obPrPay' => $obPrPay,
        ]);

    }
}
