<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\reports\forms\ActedPaymentsReportForm;
use backend\modules\reports\forms\PaymentsReportForm;
use yii\filters\AccessControl;
use common\models\CUser;
use Yii;

class ActedPaymentsReportController extends AbstractBaseBackendController{

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
                    'allow' => false,
                    'roles' => ['teamlead']
                ],
                [
                    'allow' => true,
                    'roles' => ['admin','moder','sale', 'teamlead_acc']
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
        $model = new ActedPaymentsReportForm();
        if(!\Yii::$app->user->can('adminRights'))
        {
            $model->managers = \Yii::$app->user->id;
            $arContractorMap = CUser::getContractorMapForManager(\Yii::$app->user->id);
        }else{
            $arContractorMap = CUser::getContractorMap();
        }
        $arData = [];
        if($model->load(\Yii::$app->request->post()) && $model->validate())
        {
            $arData = $model->getData();    //получаем отчет
        }
        
        return $this->render('index',[
            'model' => $model,
            'arData' => $arData,
            'arContractorMap' => $arContractorMap
        ]);
    }
} 