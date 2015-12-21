<?php

namespace app\modules\crm\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
class DefaultController extends Controller
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
    public function actionIndex()
    {
        return $this->render('index',array(

        ));
    }
}
