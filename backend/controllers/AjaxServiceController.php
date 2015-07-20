<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 16.07.15
 */

namespace backend\controllers;


use backend\components\AbstractBaseBackendController;
use common\components\managers\DialogManager;
use common\models\Dialogs;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AjaxServiceController extends AbstractBaseBackendController{

    /**
     * Контроллер по умолчанию всегда возвращает json!!!!
     */
    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::init();
    }

    public function actionAddComment()
    {
        $obDlgMng = new DialogManager([
            'iDId' => \Yii::$app->request->post('dialog_id'),
            'sMsg' => \Yii::$app->request->post('redactor'),
            'iAthID' => \Yii::$app->request->post('author_id'),
            'arUsers' => \Yii::$app->request->post('for_users')
        ]);

        return $obDlgMng->addCommentAjaxAction();
    }


} 