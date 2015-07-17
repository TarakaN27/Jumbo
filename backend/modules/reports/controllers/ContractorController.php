<?php

namespace backend\modules\reports\controllers;

use backend\components\AbstractBaseBackendController;
use backend\modules\reports\forms\ContractorReportForm;

class ContractorController extends AbstractBaseBackendController
{
    public function actionIndex()
    {
        $model = new ContractorReportForm();

        echo '<pre>';
        print_r($model->getReportData());
        echo '</pre>';
        die;


        return $this->render('index');
    }
}
