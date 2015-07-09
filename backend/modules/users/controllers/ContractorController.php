<?php

namespace backend\modules\users\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\CUserRequisites;
use Yii;
use common\models\CUser;
use common\models\search\CUserSearch;
use yii\web\NotFoundHttpException;

/**
 * ContractorController implements the CRUD actions for CUser model.
 */
class ContractorController extends AbstractBaseBackendController
{

    /**
     * Lists all CUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CUser model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $modelR = $model->requisites;
        return $this->render('view', [
            'model' => $model,
            'modelR' => $modelR
        ]);
    }

    /**
     * Creates a new CUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CUser();
        $modelR = new CUserRequisites();
        if ($model->load(Yii::$app->request->post()) && $modelR->load(Yii::$app->request->post())) {

            if($model->validate() && $modelR->validate())
            {
                $transaction = Yii::$app->db->beginTransaction(); //транзакция для того чтобы при ошибках сохранения не создавалось лишних записей
                try{
                    if($modelR->save() && $model->save())
                    {
                        $model->link('requisites',$modelR);
                        $transaction->commit();
                        Yii::$app->session->set('success',Yii::t('app/users','Contractor_successfully_added'));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }else{
                       $transaction->rollBack();
                    }
                }catch (\Exception $e)
                {
                    $transaction->rollBack();
                    Yii::$app->session->set('error',$e->getMessage());
                }
            }else{
                Yii::$app->session->set('error',Yii::t('app/users','Contractor_validate_error'));
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'modelR' => $modelR
            ]);
        }
    }

    /**
     * Updates an existing CUser model.
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
     * Deletes an existing CUser model.
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
     * Finds the CUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @param $userID
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionEditRequisites($id,$userID)
    {
        $modelU = $this->findModel($userID);
        $model = CUserRequisites::findOne($id);
        if(empty($model))
            throw new NotFoundHttpException('Requisites not found.');

        if($model->load(Yii::$app->request->post()) && $model->save())
        {
            Yii::$app->session->set('success',Yii::t('app/users','Requisites_successfully_saved'));
            return $this->redirect(['view','id'=>$userID]);
        }

        return $this->render('edit_requisites',[
            'model' => $model,
            'userID' => $userID,
            'modelU' => $modelU
        ]);

    }

    /**
     * @param $userID
     * @return string|\yii\web\Response
     */
    public function actionAddRequisites($userID)
    {
        $modelU = $this->findModel($userID);

        $model = new CUserRequisites();
        $transaction = Yii::$app->db->beginTransaction();
        if($model->load(Yii::$app->request->post()) && $model->save())
        {
            try{
                $modelU->link('requisites',$model);
                $transaction->commit();
                Yii::$app->session->set('success',Yii::t('app/users','Requisites_successfully_saved'));
            }catch (\Exception $e)
            {
                Yii::$app->session->set('error',$e->getMessage());
                $transaction->rollBack();
            }
            return $this->redirect(['view','id'=>$userID]);
        }

        return $this->render('add_requisites',[
            'model' => $model,
            'userID' => $userID,
            'modelU' => $modelU
        ]);
    }
}
