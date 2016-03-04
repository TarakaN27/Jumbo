<?php

namespace backend\modules\bookkeeping\controllers;

use common\models\Partner;
use common\models\PartnerPurse;
use Yii;
use common\models\PartnerWithdrawal;
use common\models\search\PartnerWithdrawalSearch;
use backend\components\AbstractBaseBackendController;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * PartnerWithdrawalController implements the CRUD actions for PartnerWithdrawal model.
 */
class PartnerWithdrawalController extends AbstractBaseBackendController
{
    /**
     * Lists all PartnerWithdrawal models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PartnerWithdrawalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $iTotal = $searchModel->countTotal(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'iTotal' => $iTotal
        ]);
    }

    /**
     * Displays a single PartnerWithdrawal model.
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
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new PartnerWithdrawal();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $tr = Yii::$app->db->beginTransaction();
            if($model->save()) // Добаволяем вывод средст
            {
                $obPurse  = PartnerPurse::getPurse($model->partner_id); // находим кошелек
                if(!$obPurse)
                {
                    $tr->rollBack();
                    throw new NotFoundHttpException('Partner purse not found');
                }
                $obPurse->amount-=$model->amount; //меняем сумму кошелька
                if($obPurse->save())
                {
                    $tr->commit();
                    Yii::$app->session->setFlash('success','Partner withdrawal successfully added');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                $tr->rollBack();
            }
            Yii::$app->session->setFlash('error','Partner withdrawal error');
        }

        return $this->render('create', [
                'model' => $model,
            ]);

    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $tr  = Yii::$app->db->beginTransaction();
            if($model->save())
            {
                $obPurse = PartnerPurse::getPurse($model->partner_id);
                if(!$obPurse)
                    throw new NotFoundHttpException('Partner purse not found');

                $obPurse+=$model->getDiffAmount();
                if($obPurse->save())
                {
                    $tr->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
            $tr->rollBack();
            Yii::$app->session->setFlash('error','Partner withdrawal error');
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing PartnerWithdrawal model.
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
     * Finds the PartnerWithdrawal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PartnerWithdrawal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PartnerWithdrawal::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return string
     */
    public function actionPartnerPurseAmount()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $iPID = Yii::$app->request->post('iPID');
        $model = PartnerPurse::getPurse($iPID);
        if(!$model)
            return 'N/A';
        else
            return $model->amount;
    }
}
