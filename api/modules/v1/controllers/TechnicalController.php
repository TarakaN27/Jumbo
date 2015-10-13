<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.10.15
 * Time: 14.16
 */

namespace api\modules\v1\controllers;

use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

class TechnicalController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'ping' => ['GET', 'HEAD'],
        ];
    }

    public function actionPing()
    {
        $this->checkAccessByToken();
        return TRUE;
    }

    protected function checkAccessByToken()
    {
        $token = \Yii::$app->request->get('token');
        $tokenP = \Yii::$app->params['csdaToken'];
        if($token!==$tokenP)
            throw new ForbiddenHttpException();
    }
}