<?php

namespace backend\modules\services\controllers;

use backend\components\AbstractBaseBackendController;
use backend\widgets\Alert;
use common\models\AbstractActiveRecord;
use Yii;
use common\models\PartnerExpenseCatLink;
use common\models\search\PartnerExpenseCatLinkSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use common\models\AbstractModel;
use yii\base\Exception;


/**
 * PartnerExpenseCatLinkController implements the CRUD actions for PartnerExpenseCatLink model.
 */
class PartnerExpenseCatLinkController extends AbstractBaseBackendController
{
    /**
     * Lists all PartnerExpenseCatLink models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PartnerExpenseCatLinkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PartnerExpenseCatLink model.
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
     * Creates a new PartnerExpenseCatLink model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $models =  [new PartnerExpenseCatLink()];

        if(Yii::$app->request->post('PartnerExpenseCatLink'))
        {

            $models = AbstractModel::createMultiple(PartnerExpenseCatLink::classname());
            AbstractModel::loadMultiple($models,Yii::$app->request->post());
            $valid = AbstractModel::validateMultiple($models);
            if($valid)
            {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $bFlag = TRUE;
                    /** @var PartnerExpenseCatLink $model */
                    foreach ($models as $model)
                        if(!$model->save())
                        {
                            $bFlag = FALSE;
                            break;
                        }
                    if($bFlag)
                    {
                        $transaction->commit();
                        Yii::$app->session->setFlash(Alert::TYPE_SUCCESS,Yii::t('app/users','Links successfully added'));
                        return $this->redirect(['index']);
                    }else{
                        $transaction->rollBack();
                    }
                }catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('create', [
            'models' => $models,
        ]);
    }

    /**
     * Updates an existing PartnerExpenseCatLink model.
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
     * Deletes an existing PartnerExpenseCatLink model.
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
     * Finds the PartnerExpenseCatLink model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PartnerExpenseCatLink the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PartnerExpenseCatLink::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}
