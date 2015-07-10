<?php

namespace backend\modules\users\controllers;

use backend\components\AbstractBaseBackendController;
use backend\modules\users\models\ChangePasswordBUserForm;
use Yii;
use backend\models\BUser;
use backend\models\search\BUserSearch;
use yii\web\NotFoundHttpException;

/**
 * DefaultController implements the CRUD actions for BUser model.
 */
class DefaultController extends AbstractBaseBackendController
{
    /**
     * Lists all BUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BUser model.
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
     * Creates a new BUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BUser();
        $model->setScenario(BUser::SCENARIO_REGISTER);
        $model->password = '123456'; //@todo после добавления инвайтов УДАЛИТЬ!!!!!

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing BUser model.
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
     * Deletes an existing BUser model.
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
     * Finds the BUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionChangePassword($id)
    {
        $model = new ChangePasswordBUserForm(['userID' => $id]);

        if($model->load(Yii::$app->request->post()) && $model->makeRequest())
        {
            Yii::$app->session->setFlash('success',Yii::t('app/users','Password_successfully_changed'));
            return $this->redirect(['view','id'=>$id]);
        }
        print_r($model->getErrors());

        return $this->render('change_password',[
            'model' => $model,
            'id' => $id
        ]);
    }
}
