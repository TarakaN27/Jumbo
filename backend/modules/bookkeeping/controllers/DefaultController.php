<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\models\Dialogs;
use common\models\PaymentRequest;
use Yii;
use common\models\Payments;
use common\models\search\PaymentsSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DefaultController implements the CRUD actions for Payments model.
 */
class DefaultController extends AbstractBaseBackendController
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
                    'actions' => ['index','view','create','update'],
                    'allow' => true,
                    'roles' => ['moder']
                ],
                [
                    'allow' => true,
                    'roles' => ['admin','bookkeeper']
                ]
            ]
        ];
        return $tmp;
    }

    /**
     * Lists all Payments models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if(empty($searchModel->pay_date))
            $searchModel->pay_date = NULL;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Payments model.
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
     * Creates a new Payments model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Payments();
        if(empty($model->pay_date))
            $model->pay_date = time();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Payments model.
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
     * Deletes an existing Payments model.
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
     * Finds the Payments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payments::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionCreatePaymentRequest()
    {
        $model = new PaymentRequest();
        $model->owner_id = Yii::$app->user->id;
        $model->status = PaymentRequest::STATUS_NEW;

        if($model->load(Yii::$app->request->post()) && $model->save())
        {
                $obDlg = new Dialogs();
                $obDlg->type = Dialogs::TYPE_REQUEST;
                $obDlg->buser_id = Yii::$app->user->id;
                $obDlg->status = Dialogs::PUBLISHED;
                $obDlg->theme = Yii::t('app/book','New payment request').'<br>'.$model->description;
                if($obDlg->save())
                {
                    if(!empty($model->manager_id))
                    {
                        $obManager = BUser::findOne($model->manager_id);
                        if(empty($obManager))
                            throw new NotFoundHttpException('Manager not found');
                        $obDlg->link('busers',$obManager);
                    }else{
                        $obManagers = BUser::getManagersArr();
                        if(!empty($obManagers))
                            foreach($obManagers as $obMan)
                                $obDlg->link('busers',$obMan);
                    }
                    Yii::$app->session->setFlash('success',Yii::t('app/common','DIALOG_SUCCESS_ADD_DIALOG'));
                }else{
                    Yii::$app->session->setFlash('error',Yii::t('app/common','DIALOG_ERROR_ADD_DIALOG'));
                }
            Yii::$app->session->setFlash('success',Yii::t('app/book','New payment request successfully added'));
            return $this->redirect(['index']);
        }
        return $this->render('create_payment_request',[
            'model' => $model
        ]);
    }
}
