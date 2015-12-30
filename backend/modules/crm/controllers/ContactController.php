<?php

namespace app\modules\crm\controllers;

use backend\widgets\Alert;
use common\models\CrmCmpFile;
use common\models\search\CrmTaskSearch;
use Yii;
use common\models\CrmCmpContacts;
use common\models\search\CrmCmpContactsSearch;
use backend\components\AbstractBaseBackendController;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use common\models\BUserCrmRules;
use yii\web\Response;
use yii\filters\AccessControl;
/**
 * ContactController implements the CRUD actions for CrmCmpContacts model.
 */
class ContactController extends AbstractBaseBackendController
{

    /**
     * переопределяем права на контроллер и экшены
     * @return array
     */
    public function behaviors()
    {
        $tmp = parent::behaviors();
        $tmp['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['moder','bookkeeper','admin']
                ]
            ]
        ];
        return $tmp;
    }

    /**
     * Lists all CrmCmpContacts models.
     * @return mixed
     */
    public function actionIndex()
    {
        $iAccessLevel = \Yii::$app->user->getCRMLevelAccess(CrmCmpContacts::getModelName(),BUserCrmRules::READ_ACTION);
        $searchModel = new CrmCmpContactsSearch();
        $dataProvider = NULL;
        switch($iAccessLevel)
        {
            case BUserCrmRules::RULE_ALL:
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                break;

            case BUserCrmRules::RULE_THEMSELF:
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams,
                    '('.CrmCmpContacts::tableName().'.assigned_at = :userID OR '.CrmCmpContacts::tableName().'.created_by = :userID' ,
                    [
                        ':userID' => Yii::$app->user->id
                    ]);
                break;

            case BUserCrmRules::RULE_OPENED:
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams,[
                    'is_opened' => CrmCmpContacts::IS_OPENED
                ]);
                break;

            default:
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams,'1=0'); //ничего не найдем
                break;
        }

        // Get the initial city description
        $cuserDesc = empty($searchModel->cmp_id) ? '' : \common\models\CUser::findOne($searchModel->cmp_id)->getInfo();
        $buserDesc = empty($searchModel->assigned_at) ? '' : \backend\models\BUser::findOne($searchModel->assigned_at)->getFio();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cuserDesc' => $cuserDesc,
            'buserDesc' => $buserDesc
        ]);
    }

    /**
     * Displays a single CrmCmpContacts model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $arFiles = $model->files;
        $obFile = new CrmCmpFile();

        if(!empty($model->cmp_id))
            $obFile->cmp_id = $model->cmp_id;
        $obFile->contact_id = (int)$id;
        $obFile->setScenario('insert');
        /**
         * добавление файла
         */
        if($obFile->load(Yii::$app->request->post()))
        {
            if($obFile->save())
                Yii::$app->session->setFlash(Alert::TYPE_SUCCESS,Yii::t('app/crm','File successfully added'));
            else
                Yii::$app->session->setFlash(Alert::TYPE_ERROR,Yii::t('app/crm','Error. Can not add file.'));

            return $this->redirect(Url::current());
        }
        /**
         * смена ответсвенного
         */
        if($model->load(Yii::$app->request->post()))
        {
            if($obFile->save())
                Yii::$app->session->setFlash(Alert::TYPE_SUCCESS,Yii::t('app/crm','Assigned changed successfully'));
            else
                Yii::$app->session->setFlash(Alert::TYPE_ERROR,Yii::t('app/crm','Error. Can not change assigned.'));

            return $this->redirect(Url::current());
        }
        //Задачи
        $obCrmTaskSearch = new CrmTaskSearch();
        $dataProviderTask = $obCrmTaskSearch->search(
            Yii::$app->request->queryParams,
            CrmTaskSearch::VIEW_TYPE_ALL,
            ['contact_id' => $model->id]
        );

        return $this->render('view', [
            'model' => $model,
            'arFiles' => $arFiles,
            'obFile' => $obFile,
            'dataProviderTask' => $dataProviderTask
        ]);
    }

    /**
     * Creates a new CrmCmpContacts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CrmCmpContacts();
        $model->assigned_at = Yii::$app->user->id;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            // Get the initial city description
            $cuserDesc = empty($model->cmp_id) ? '' : \common\models\CUser::findOne($model->cmp_id)->getInfo();
            $buserDesc = empty($model->assigned_at) ? '' : \backend\models\BUser::findOne($model->assigned_at)->getFio();

            return $this->render('create', [
                'model' => $model,
                'cuserDesc' => $cuserDesc,
                'buserDesc' => $buserDesc
            ]);
        }
    }

    /**
     * Updates an existing CrmCmpContacts model.
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
            // Get the initial city description
            $cuserDesc = empty($model->cmp_id) ? '' : \common\models\CUser::findOne($model->cmp_id)->getInfo();
            $buserDesc = empty($model->assigned_at) ? '' : \backend\models\BUser::findOne($model->assigned_at)->getFio();
            return $this->render('update', [
                'model' => $model,
                'cuserDesc' => $cuserDesc,
                'buserDesc' => $buserDesc
            ]);
        }
    }

    /**
     * Deletes an existing CrmCmpContacts model.
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
     * Finds the CrmCmpContacts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CrmCmpContacts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CrmCmpContacts::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return false|int
     * @throws NotFoundHttpException
     */
    public function actionDeleteFile()
    {
        $pk = Yii::$app->request->post('pk');
        $obFile = CrmCmpFile::findOne($pk);
        if(!$obFile)
            throw new NotFoundHttpException('File not found');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $obFile->delete();
    }

    /**
     * @param $id
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionDownloadFile($id)
    {
        $obFile = CrmCmpFile::findOne(['id' => $id]);
        if(!$obFile)
            throw new NotFoundHttpException('File not found');
        return Yii::$app->response->sendFile($obFile->getFilePath());
    }
}
