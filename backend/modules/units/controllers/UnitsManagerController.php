<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */

namespace backend\modules\units\controllers;


use backend\components\AbstractBaseBackendController;
use yii\filters\AccessControl;
use backend\modules\reports\forms\DetailUnitsViewForm;


class UnitsManagerController extends AbstractBaseBackendController{

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
                    'roles' => ['admin','moder']
                ]
            ]
        ];
        return $tmp;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dateFrom = date('Y').'-'.date('m').'-1';
        $dateTo = date('Y').'-'.date('m').'-'.date('d');

        $model = new DetailUnitsViewForm([
            'manID' => \Yii::$app->user->id,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);

        $model->load(\Yii::$app->request->post());

        $arData = [];
        if($valid = $model->validate())
            $arData = $model->makeRequest();

        return $this->render('index',[
            'arData' => $arData,
            'model' => $model
        ]);
    }
} 