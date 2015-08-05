<?php

namespace backend\modules\helpers\controllers;

use backend\modules\helpers\LoadXmlFileForm;
use yii\web\Controller;
use yii\web\UploadedFile;

class DefaultController extends Controller
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
