<?php

namespace backend\modules\bonus\controllers;

use Yii;
use common\models\BonusScheme;
use common\models\search\BonusSchemeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use common\models\Services;

/**
 * DefaultController implements the CRUD actions for BonusScheme model.
 */
class DefaultController extends Controller
{
    /**
     * Lists all BonusScheme models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BonusSchemeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BonusScheme model.
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
     * Creates a new BonusScheme model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BonusScheme();


        echo '<pre>';
        print_r($_POST);
        echo '</pre>';

        //die;
        if ($model->load(Yii::$app->request->post())) {
            $tr = Yii::$app->db->beginTransaction();
            $arServices = Services::getAllServices();
            if($model->save())
            {
                $cost = Yii::$app->request->post('cost');
                $multiple = Yii::$app->request->post('multiple');
                $monthPersent = Yii::$app->request->post('services',[]);




                return $this->redirect(['view', 'id' => $model->id]);
            }
            $tr->rollBack();
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing BonusScheme model.
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
     * Deletes an existing BonusScheme model.
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
     * Finds the BonusScheme model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BonusScheme the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BonusScheme::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
