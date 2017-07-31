<?php

namespace backend\modules\bookkeeping\controllers;

use common\models\CUser;
use common\models\Services;
use Yii;
use common\models\Enrolls;
use common\models\search\EnrollsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use common\models\ExchangeCurrencyHistory;

/**
 * EnrollsController implements the CRUD actions for Enrolls model.
 */
class EnrollsController extends Controller
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
                    'actions' => [
                        'view',
                        'update',
                        'delete'
                    ],
                    'allow' => true,
                    'roles' => ['admin']
                ],
                [
                    'actions' => [
                        'index'
                    ],
                    'allow' => true,
                    'roles' => ['admin','bookkeeper','moder']
                ]
            ]
        ];
        return $tmp;
    }

    /**
     * Lists all Enrolls models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EnrollsSearch();

        $addQuery = [];
        $addParams = [];

        if(Yii::$app->user->can('only_manager'))
        {
            $addQuery = '( buser_id = :user OR '.CUser::tableName().'.manager_id = :user OR serv.b_user_enroll = :user )';
            $addParams = [
                ':user' => Yii::$app->user->id
            ];
        }


        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$addQuery,$addParams); //table render data
        $arTotal = $searchModel->totalCount(Yii::$app->request->queryParams,$addQuery,$addParams); //total table render data
        $cuserDesc = '';
        if(!empty($searchModel->cuser_id))
        {
            $obUser = CUser::findOne($searchModel->cuser_id);
            if($obUser)
                $cuserDesc = $obUser->getInfo();
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cuserDesc' => $cuserDesc,
            'arTotal' => $arTotal
        ]);
    }

    /**
     * Displays a single Enrolls model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $searchModel = new EnrollsSearch();
        return $this->render('view', [
            'model' => $searchModel->getEnrollInfoWithRate($id),
        ]);
    }

    /**
     * Updates an existing Enrolls model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $exchRate = null;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            if(!empty($model->enrReq->payment) && !empty($model->enrReq->payment->calculate->payCond))
            {
                $exchRate = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$model->enrReq->payment->pay_date),$model->enrReq->payment->calculate->payCond->cond_currency);
            }
            return $this->render('update', [
                'model' => $model,
                'exchRate' =>$exchRate,
            ]);
        }
    }

    /**
     * Deletes an existing Enrolls model.
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
     * Finds the Enrolls model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Enrolls the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Enrolls::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
