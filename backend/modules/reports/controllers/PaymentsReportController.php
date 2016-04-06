<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\reports\forms\PaymentsReportForm;

class PaymentsReportController extends AbstractBaseBackendController{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $model = new PaymentsReportForm();
        $arData = [];
        if($model->load(\Yii::$app->request->post()) && $model->validate())
        {
            $arData = $model->getData();    //получаем отчет
        }
        return $this->render('index',[
            'model' => $model,
            'arData' => $arData
        ]);
    }
} 