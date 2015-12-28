<?php

namespace backend\modules\documents\controllers;

use common\components\helpers\Docx_reader;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use Yii;
use common\models\ActsTemplate;
use common\models\search\ActsTemplateSearch;
use backend\components\AbstractBaseBackendController;
use yii\web\NotFoundHttpException;

/**
 * ActsTemplateController implements the CRUD actions for ActsTemplate model.
 */
class ActsTemplateController extends AbstractBaseBackendController
{
    /**
     * Lists all ActsTemplate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActsTemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ActsTemplate model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,

        ]);
    }

    /**
     * Creates a new ActsTemplate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ActsTemplate();
        $model->scenario = 'insert';
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ActsTemplate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ActsTemplate model.
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
     * Finds the ActsTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActsTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ActsTemplate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionDownloadFile($id)
    {
        $obModel = $this->findModel($id);
        return Yii::$app->response->sendFile($obModel->getFilePath());
    }
}
