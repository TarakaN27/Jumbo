<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\CUser;
use Yii;
use common\models\PromisedPayment;
use common\models\search\PromisedPaymentSearch;
use yii\web\NotFoundHttpException;

/**
 * PromisedPaymentController implements the CRUD actions for PromisedPayment model.
 */
class PromisedPaymentController extends AbstractBaseBackendController
{


    /**
     * Lists all PromisedPayment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $arSearchParam = [];
        if(Yii::$app->user->isManager())
        {
            $tmp = CUser::getContractorForManager(Yii::$app->user->id);
            $arUserID = [];
            foreach($tmp as $t)
                $arUserID [] = $t->id;

            $arSearchParam ['cuser_id'] = $arUserID;
        }


        $searchModel = new PromisedPaymentSearch($arSearchParam);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PromisedPayment model.
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
     * Creates a new PromisedPayment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PromisedPayment();

        $model->setScenario(PromisedPayment::SCENARIO_NEW); //устанавливаем сценарий для валидации
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PromisedPayment model.
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
     * Deletes an existing PromisedPayment model.
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
     * Finds the PromisedPayment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PromisedPayment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PromisedPayment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
