<?php
namespace api\modules\v1\controllers;
use api\components\AbstractActiveActionREST;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 30.9.15
 * Time: 16.51
 */
class ServiceController extends AbstractActiveActionREST
{
    public $modelClass = 'common\models\Services';

    /**
     * @return array
     */
    protected function verbs()
    {
        $tmp = parent::verbs();
        return ArrayHelper::merge($tmp,[
            'get-services' => ['POST']
        ]);
    }

    public function actionGetServices()
    {
        $this->checkAccessByToken();
        $userSC = \Yii::$app->request->post('users-secret-keys');




        return [];
    }

}