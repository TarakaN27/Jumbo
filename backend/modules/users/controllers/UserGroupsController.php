<?php

namespace backend\modules\users\controllers;

use common\models\CUser;
use common\models\CUserRequisites;
use Yii;
use common\models\CUserGroups;
use common\models\search\CUserGroupsSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * UserGroupsController implements the CRUD actions for CUserGroups model.
 */
class UserGroupsController extends Controller
{
    /**
     * Lists all CUserGroups models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CUserGroupsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CUserGroups model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $arCuser = $model->cuserObjects;

        return $this->render('view', [
            'model' => $this->findModel($id),
            'arCuser' => $arCuser
        ]);
    }

    /**
     * Creates a new CUserGroups model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CUserGroups();

        if ($model->load(Yii::$app->request->post()) && $model->saveWithCUser(TRUE)) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $data = [];
            if(!empty($model->cuserIds))
            {
                $arCuser = CUser::find()
                    ->select([
                        CUser::tableName().'.id',
                        CUser::tableName().'.type',
                        CUserRequisites::tableName().'.type_id',
                        'requisites_id',
                        CUserRequisites::tableName().'.corp_name',
                        CUserRequisites::tableName().'.j_fname',
                        CUserRequisites::tableName().'.j_lname',
                        CUserRequisites::tableName().'.j_mname',
                    ])
                    ->joinWith('requisites')
                    ->where([CUser::tableName().'.id' => $model->cuserIds])
                    ->all();
                $data = ArrayHelper::map($arCuser,'id','infoWithSite');
            }
            var_dump($model->getErrors());
            return $this->render('create', [
                'model' => $model,
                'data' => $data
            ]);
        }
    }

    /**
     * Updates an existing CUserGroups model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $arCuser = $model->cUser;
        if($arCuser)
            foreach($arCuser as $acc) {
                $model->cuserIds [] = $acc->cuser_id;
            }
        if ($model->load(Yii::$app->request->post()) && $model->saveWithCUser(TRUE)) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $arCuser = CUser::find()
                ->select([
                    CUser::tableName().'.id',
                    CUser::tableName().'.type',
                    CUserRequisites::tableName().'.type_id',
                    'requisites_id',
                    CUserRequisites::tableName().'.corp_name',
                    CUserRequisites::tableName().'.j_fname',
                    CUserRequisites::tableName().'.j_lname',
                    CUserRequisites::tableName().'.j_mname',
                ])
                ->joinWith('requisites')
                ->where([CUser::tableName().'.id' => $model->cuserIds])
                ->all();
            $data = ArrayHelper::map($arCuser,'id','infoWithSite');
            return $this->render('update', [
                'model' => $model,
                'data' => $data
            ]);
        }
    }

    /**
     * Deletes an existing CUserGroups model.
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
     * Finds the CUserGroups model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CUserGroups the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CUserGroups::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
