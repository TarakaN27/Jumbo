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
use common\models\Messages;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class AjaxServiceController extends AbstractBaseBackendController{

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
                    'roles' => ['@'],
                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'add-comment' => ['post'],
                        'add-message' => ['post'],
                        'load-dialog' => ['post'],
                        'add-new-message' => ['post'],
                        'add-dialog' => ['post']
                    ],
                ],
            ]
        ];
        return $tmp;
    }
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

    public function actionLoadDialog()
    {
        $iDID = \Yii::$app->request->post('iDID');
        $iPage = \Yii::$app->request->post('iPage');
        if(empty($iPage))
            $iPage = 0;

        $obDlg = new DialogManager(['iDId' => $iDID]);
        $dlg = $obDlg->loadDialog($iPage);
        return ['content' => $this->renderPartial('_load_dialog',[
                'models' => $dlg['models'],
                'pages' => $dlg['pages'],
                'iDID' => $iDID,
                'addLoadMoreBTN' => TRUE
            ])];
    }

    public function actionAddNewMessage()
    {
        $iDID = \Yii::$app->request->post('iDID');
        $sContent = \Yii::$app->request->post('content');

        if(empty($iDID) || empty($sContent))
            throw new NotFoundHttpException('Required params not found.');

        $msg = new Messages([
            'buser_id' => \Yii::$app->user->id,
            'dialog_id' => $iDID,
            'parent_id' => 0,
            'lvl' => 0,
            'status' => Messages::PUBLISHED,
            'msg'=>$sContent ,
        ]);

        if(!$msg->save())
            throw new ServerErrorHttpException("Cant't save the message");

        return [
            'content' => $this->renderPartial('_load_dialog',[
                    'models' => [$msg],
                    'addLoadMoreBTN' => FALSE,
                    'pages' => NULL
                ]),
            'iDID' => $iDID

        ];
    }

    public function actionAddDialog()
    {
        $obDlgMng = new DialogManager([
            'iDId' => \Yii::$app->request->post('dialog_id'),
            'sMsg' => \Yii::$app->request->post('redactor'),
            'iAthID' => \Yii::$app->request->post('author_id'),
            'arUsers' => \Yii::$app->request->post('for_users')
        ]);

        if($obDlg = $obDlgMng->addDialog())
        {
            return [
                'content' => $this->renderPartial('@backend/modules/messenger/views/default/_dialog_left_part.php',[
                        'model' => $obDlg
                    ])
            ];
        }

        throw new ServerErrorHttpException("Can't create dialog");
    }


} 