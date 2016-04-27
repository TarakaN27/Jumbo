<?php

namespace backend\modules\partners\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\models\CUser;
use Yii;
use common\models\PartnerWithdrawalRequest;
use common\models\search\PartnerWithdrawalRequestSearch;
use yii\web\NotFoundHttpException;


/**
 * PartnerWithdrawalRequestController implements the CRUD actions for PartnerWithdrawalRequest model.
 */
class PartnerWithdrawalRequestController extends AbstractBaseBackendController
{
    /**
     * Lists all PartnerWithdrawalRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PartnerWithdrawalRequestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $partnerDesc = '';
        if($searchModel->partner_id)
            $partnerDesc = is_object($obP = CUser::find()->joinWith('requisites')->where([CUser::tableName().'.id' => $searchModel->partner_id])->one()) ? $obP->getInfoWithSite() : NULL;

        $managerDesc = '';
        if($searchModel->manager_id)
            $managerDesc = is_object($obMan = BUser::find()->where(['id' => $searchModel->manager_id])->one()) ? $obMan->getFio() : NULL;

        $pManDesc = '';
        if($searchModel->partnerManager)
            $pManDesc = is_object($obPMan = BUser::find()->where(['id' => $searchModel->partnerManager])->one()) ? $obPMan->getFio() : NULL;

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
}
