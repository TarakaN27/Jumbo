<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.4.16
 * Time: 17.14
 */

namespace backend\modules\partners\controllers;


use backend\components\AbstractBaseBackendController;
use common\models\search\CUserSearch;
use Yii;

class PartnersController extends AbstractBaseBackendController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CUserSearch();
        $dataProvider = $searchModel->searchPartners(Yii::$app->request->queryParams);

        return $this->render('index',[
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
}