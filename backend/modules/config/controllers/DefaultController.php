<?php

namespace backend\modules\config\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\Config;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\filters\AccessControl;

class DefaultController extends AbstractBaseBackendController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        $arTmp = parent::behaviors();
        $arTmp['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['superadmin'],
                ],
            ],
        ];
        return $arTmp;
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Config::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index',[
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionUpdate()
    {
        $id = \Yii::$app->request->post('pk');
        $value = \Yii::$app->request->post('value');

        if(empty($id) || empty($value))
            throw new InvalidParamException('id or value not set');

        $obConfig = Config::findOne($id);
        if(empty($obConfig))
            throw new NotFoundHttpException();

        $obConfig->value = $value;
        if(!$obConfig->save())
            throw new ServerErrorHttpException();

        \Yii::$app->end();
    }
}
