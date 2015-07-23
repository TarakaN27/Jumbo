<?php
/**
 * Created by PhpStorm.
 * Corp: ZM_TEAM
 * User: E. Motuz
 * Date: 7/21/15
 * Time: 11:34 PM
 */

namespace backend\modules\bookkeeping\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\bookkeeping\form\AddPaymentForm;
use common\models\AbstractModel;
use common\models\PaymentRequest;
use common\models\search\PaymentRequestSearch;
use Yii;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class PaymentRequestController extends AbstractBaseBackendController{

    public function actionIndex()
    {
        $searchModel = new PaymentRequestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if(empty($searchModel->pay_date))
            $searchModel->pay_date = NULL;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAddPayment($pID)
    {
        $modelP = PaymentRequest::findOne($pID);
        if(empty($modelP))
            throw new NotFoundHttpException('Payment request not found');

        //if($modelP->manager_id !== Yii::$app->user->id)
        //    throw new ForbiddenHttpException('You are not allowed to perform this action')
        if(!Yii::$app->request->post('AddPaymentForm'))
            $model = [new AddPaymentForm(['fullSumm' => $modelP->pay_summ])];
        else
        {
            $model = AbstractModel::createMultiple(AddPaymentForm::classname());
            AbstractModel::loadMultiple($model,Yii::$app->request->post());
            $valid = AbstractModel::validateMultiple($model);

            if($valid)
            {
                $transaction = \Yii::$app->db->beginTransaction();
                try {

                }catch(Exception $e){
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('add_payment',[
            'model' => $model,
            'modelP' => $modelP
        ]);
    }

    public function actionView($id)
    {
        $model = PaymentRequest::find()
            ->where(['id' => $id])
            ->with('owner','legal','currency','cuser')
            ->one();

        return $this->render('view',[
            'model' => $model
        ]);
    }

} 