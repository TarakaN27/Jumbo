<?php

namespace backend\modules\documents\controllers;

use common\models\BillTemplate;
use common\models\LegalPerson;
use common\models\managers\BillsManager;
use Yii;
use common\models\Bills;
use common\models\search\BillsSearch;
use backend\components\AbstractBaseBackendController;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * BillsController implements the CRUD actions for Bills model.
 */
class BillsController extends AbstractBaseBackendController
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
                    'roles' => ['superadmin','moder']
                ]
            ]
        ];
        $tmp['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'delete' =>                     ['post'],
                'find-bill-template' =>         ['post'],
                'get-bill-template-detail' =>   ['post']
            ],
        ];

        return $tmp;
    }

    /**
     * Lists all Bills models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BillsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Bills model.
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
     * Creates a new Bills model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Bills();

        if(Yii::$app->user->can('only_manager'))
            $model->manager_id = Yii::$app->user->id;

        $model->buy_target = Yii::t('app/documents','DefaultBuytarget');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            print_r($model->getErrors());
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Bills model.
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
     * Deletes an existing Bills model.
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
     * Finds the Bills model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Bills the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Bills::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionFindBillTemplate()
    {
        $iServID = Yii::$app->request->post('iServID');
        $lPID = Yii::$app->request->post('lPID');

        if(empty($iServID) || empty($lPID))
            throw new InvalidParamException();
        $model = BillTemplate::find()->where([ 'l_person_id' => $lPID,'service_id' => $iServID])->one();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return empty($model) ? '' : $model;
    }

    /**
     * @return mixed|null|string|static
     * @throws \yii\base\InvalidParamException
     */
    public function actionGetBillTemplateDetail()
    {
        $iBTpl = Yii::$app->request->post('iBTpl');
        if(empty($iBTpl))
            throw new InvalidParamException();

        $model = BillTemplate::findOneByIDCached($iBTpl);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return empty($model) ? '' : $model;
    }

    public function actionGetBill($id,$type)
    {
        $model = BillsManager::findOneByIDCached($id);
        $model->getDocument($type);
        Yii::$app->end();
    }

    public function actionFindLegalPerson()
    {
        $lPID = Yii::$app->request->post('lPID');
        $model = LegalPerson::findOneByIDCached($lPID);
        if(empty($model))
            throw new NotFoundHttpException('Legal person not found');
        return $model->use_vat;
    }
}
