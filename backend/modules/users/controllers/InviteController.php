<?php

namespace backend\modules\users\controllers;

use Yii;
use common\models\BuserInviteCode;
use common\models\search\BuserInviteCodeSearch;
use backend\components\AbstractBaseBackendController;
use yii\web\NotFoundHttpException;


/**
 * InviteController implements the CRUD actions for BuserInviteCode model.
 */
class InviteController extends AbstractBaseBackendController
{
    /**
     * Lists all BuserInviteCode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BuserInviteCodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @return string|\yii\web\Response
     */
    public function actionAddInvite()
    {
        $uID = Yii::$app->user->id;
        $model = new BuserInviteCode(['buser_id' => $uID]);

        if($model->load(Yii::$app->request->post()) && $model->save())
        {
            if($model->sendEmail())
            {
                Yii::$app->session->setFlash('success',Yii::t('app/common','Thank you! Invite was successfully send'));
                return $this->redirect(['index']);
            }else
            {
                Yii::$app->session->setFlash('error',Yii::t('app/common','Sorry! We have same error, please try again!'));
            }
        }
        return $this->render('add_invite',[
            'model' => $model
        ]);
    }


    /**
     * Deletes an existing BuserInviteCode model.
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
     * Finds the BuserInviteCode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BuserInviteCode the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BuserInviteCode::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionResend($id)
    {
        $model  = $this->findModel($id);
        if($model->resend())
            Yii::$app->session->setFlash('success',Yii::t('app/common','Invite resend'));
        else
            Yii::t('app/common','Error, please try again!');

        return $this->redirect(['index']);
    }
}
