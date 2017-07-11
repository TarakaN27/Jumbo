<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\reports\forms\ExpenseReportForm;
use common\models\ExpenseCategories;
use common\models\LegalPerson;
use yii\filters\AccessControl;
use common\models\CUser;
use Yii;

class ExpenseReportController extends AbstractBaseBackendController{

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

        $model = new ExpenseReportForm();
        if(!\Yii::$app->user->can('adminRights'))
        {
            $model->managers = \Yii::$app->user->id;
            $arContractorMap = CUser::getContractorMapForManager(\Yii::$app->user->id);
        }else{
            $arContractorMap = CUser::getExpenseUserMap();
        }

        $arData = [];
        if($model->load(\Yii::$app->request->post()) && $model->validate())
        {
            $arData = $model->getData();    //получаем отчет
        }

        $arContractorMap[-1] = Yii::t('app/reports','Without contractor');
        ksort($arContractorMap);
        //array_unshift($arContractorMap, 'Без контрагента');
        //var_dump(Yii::$app->request->post()); die;

        return $this->render('index',[
            'model' => $model,
            'arData' => $arData,
            'arContractorMap' => $arContractorMap
        ]);
    }
} 