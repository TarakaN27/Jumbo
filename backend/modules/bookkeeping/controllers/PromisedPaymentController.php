<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\models\CUser;
use common\models\EnrollmentRequest;
use common\models\Enrolls;
use Yii;
use common\models\PromisedPayment;
use common\models\search\PromisedPaymentSearch;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * PromisedPaymentController implements the CRUD actions for PromisedPayment model.
 */
class PromisedPaymentController extends AbstractBaseBackendController
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
                    'allow' => TRUE,
                    'roles' => ['admin', 'bookkeeper', 'moder']
                ]
            ]
        ];

        return $tmp;
    }

    /**
     * Lists all PromisedPayment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $addQuery = [];
        if (Yii::$app->user->isManager()) {
            $tmp = CUser::getContractorForManager(Yii::$app->user->id);
            $arUserID = [];
            foreach ($tmp as $t)
                $arUserID [] = $t['id'];
            $addQuery [PromisedPayment::tableName() . '.cuser_id'] = $arUserID;
        }

        $searchModel = new PromisedPaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $addQuery);

        $arTotal = $searchModel->countTotal(Yii::$app->request->queryParams, $addQuery);
        $cuserDesc = empty($searchModel->cuser_id) ? '' : \common\models\CUser::findOne($searchModel->cuser_id)->getInfoWithSite();
        $buserDesc = empty($searchModel->buser_id_p) ? '' : BUser::findOne($searchModel->buser_id_p)->getFio();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'arTotal' => $arTotal,
            'cuserDesc' => $cuserDesc,
            'buserDesc' => $buserDesc
        ]);
    }

    /**
     * Displays a single PromisedPayment model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $obRepay = $model->repay;
        return $this->render('view', [
            'model' => $model,
            'obRepay' => $obRepay
        ]);
    }

    /**
     * Creates a new PromisedPayment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PromisedPayment();
        $model->owner = Yii::$app->user->id;
        $tr = Yii::$app->db->beginTransaction();
        try {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $tr->commit();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } catch (\Exception $e) {
            $tr->rollBack();
            Yii::$app->session->setFlash('error', 'Error');
            return $this->redirect(['create']);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PromisedPayment model.
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
     * Finds the PromisedPayment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PromisedPayment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PromisedPayment::findOne($id)) !== NULL) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return int
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionChangePaid()
    {
        $pk = Yii::$app->request->post('pk');
        if (empty($pk))
            throw new InvalidParamException();

        $model = $this->findModel($pk);

        if ($model->paid) {
            $model->buser_id_p = '';
            $model->paid_date = '';
            $model->paid = PromisedPayment::NO;
        } else {
            $model->buser_id_p = Yii::$app->user->id;
            $model->paid_date = time();
            $model->paid = PromisedPayment::YES;
        }

        if (!$model->save())
            throw new ServerErrorHttpException();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model->paid;
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $approve = Yii::$app->request->post('approve');
        $result = [];
        $enrollsForDelete = [];
        $enrollsForModification = [];
        $enrollRequestForDelete = [];
        $promisePaymentForDelete = [];
        $promisePaymentForUpdate = [];
        $promisePayment = PromisedPayment::findOne($id);
        if ($promisePayment) {
            if ($promisePayment->paid == 1) {
                $result['is_paid'] = true;
            } else {
                $paidSum = 0;
                if ($promisePayment->repay) {
                    foreach ($promisePayment->repay as $item) {
                        $paidSum += $item->amount;
                    }
                    $promisePayment->paid = 1;
                    $promisePayment->amount = $paidSum;
                    $promisePaymentForUpdate[] = $promisePayment;
                } else {
                    $promisePaymentForDelete[] = $promisePayment;
                }
                $enrolls = [];

                $enrollRequest = EnrollmentRequest::find()->where(['pr_payment_id' => $id])->all();
                foreach ($enrollRequest as $item) {
                    if ($item->status == 10) {
                        $enrollsTemp = Enrolls::find()->where(['enr_req_id' => $item->id])->orderBy(['created_at' => SORT_ASC])->all();
                        foreach ($enrollsTemp as $item) {
                            $enrolls[] = $item;
                        }
                    } else {
                        $enrollRequestForDelete[] = $item;
                    }
                }
                foreach ($enrolls as $item) {
                    if ($paidSum >= $item->amount) {
                        $paidSum -= $item->amount;
                    } elseif ($paidSum > 0 && $paidSum < $item->amount) {
                        $item->amount = $paidSum;
                        $paidSum = 0;
                        $enrollsForModification[] = $item;
                    } else {
                        $enrollsForDelete[] = $item;
                    }
                }
            }
        }
        if ($approve == 'false') {
            $result['enrollRequestForDelete'] = $enrollRequestForDelete;
            $result['enrollsForDelete'] = $enrollsForDelete;
            $result['enrollsForModification'] = $enrollsForModification;
            $result['promisePaymentForDelete'] = $promisePaymentForDelete;
            $result['promisePaymentForUpdate'] = $promisePaymentForUpdate;
            return json_encode($this->renderPartial('_del_pay_records', [
                'result' => $result,
            ]));
        }
        if ($approve == 'true') {
            foreach ($enrollRequestForDelete as $item)
                $item->delete();
            foreach ($enrollsForDelete as $item)
                $item->delete();
            foreach ($enrollsForModification as $item)
                $item->save();
            foreach ($promisePaymentForDelete as $item)
                $item->delete();
            foreach ($promisePaymentForUpdate as $item)
                $item->save();
            return json_encode(['approve' => 'done']);
        }
        if ($approve == 'done') {
            return $this->redirect(['index']);
        }
    }
}
