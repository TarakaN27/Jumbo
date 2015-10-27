<?php
namespace api\components;
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.10.15
 * Time: 14.34
 */

use yii\web\ForbiddenHttpException;
class AbstractActiveActionREST extends \yii\rest\ActiveController
{

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if(parent::beforeAction($action))
        {
            $this->checkAccessByToken();
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @throws ForbiddenHttpException
     */
    protected function checkAccessByToken()
    {
        $token = \Yii::$app->request->get('token');
        $tokenP = \Yii::$app->params['csdaToken'];
        if($token!==$tokenP)
            throw new ForbiddenHttpException();
    }

}