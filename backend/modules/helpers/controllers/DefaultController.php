<?php

namespace backend\modules\helpers\controllers;

use backend\components\AbstractBaseBackendController;
use backend\modules\helpers\forms\LoadXmlFileForm;
use yii\web\UploadedFile;

class DefaultController extends AbstractBaseBackendController
{
    public function actionIndex()
    {
        $model = new LoadXmlFileForm();

        if($model->load(\Yii::$app->request->post()))
        {
            $model->file = UploadedFile::getInstance($model, 'file');

            if($model->makeRequest())
            {
                \Yii::$app->session->setFlash('success','Load file successfully');
                $this->redirect(['index']);
            }
        }
        return $this->render('index',[
            'model' =>$model
        ]);
    }
}
