<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */

namespace backend\modules\reports\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\reports\forms\DetailUnitsViewForm;
use backend\modules\reports\forms\UnitsReportForm;

class UnitsReportsController extends AbstractBaseBackendController{

    public function actionIndex()
    {
        $model = new UnitsReportForm();
        $arData = [];
        if($model->load(\Yii::$app->request->post()) && $model->validate())
        {
            $arData = $model->getData();
        }


        return $this->render('index',[
           'arData' => $arData,
           'model' => $model
        ]);
    }

    /**
     * @param $id
     * @return array
     */
    public function actionView($id)
    {
        $dateFrom =  \Yii::$app->request->get('dateFrom');
        $dateTo = \Yii::$app->request->get('dateTo');

        if(empty($dateFrom))
            $dateFrom = date('Y').'-'.date('m').'-1';

        if(empty($dateTo))
            $dateTo = date('Y').'-'.date('m').'-'.date('t');

        $model = new DetailUnitsViewForm([
            'manID' => $id,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);

        $model->load(\Yii::$app->request->post());

        $arData = [];
        if($valid = $model->validate())
            $arData = $model->makeRequest();

        return $this->render('view',[
            'arData' => $arData,
            'model' => $model
        ]);
    }

} 