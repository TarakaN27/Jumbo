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
use common\models\CrmTask;
use common\models\Dialogs;
use common\models\ExchangeCurrencyHistory;
use common\models\managers\ExchangeRatesManager;
use common\models\Messages;
use common\models\PartnerPurse;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use Yii;
use common\components\notification\RedisNotification;

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
                        'add-dialog' => ['post'],
                        'add-new-dialog' => ['post'],
                        'flush-notification' => ['post'],
                        'load-exchange-rates' => ['post']
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

    /**
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function actionLoadLfDialogs()
    {
        $page = \Yii::$app->request->post('page');
        if(empty($page))
            return NULL;

        $obDMan = new DialogManager(['userID' => \Yii::$app->user->id]);
        $arDialogs= $obDMan->loadLiveFeedDialogs((int)$page);

        $arRedisDialog = RedisNotification::getDialogListForUser(Yii::$app->user->id);

        return $this->renderPartial('@common/components/widgets/liveFeed/views/_dialog_part.php',[
            'arDialogs' => $arDialogs,
            'pages' => $obDMan->getPages(),
            'arRedisDialog' => $arRedisDialog
        ]);
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAddNewDialog()
    {
        $iCmpID = Yii::$app->request->post('cmp_id');
        $sMsg = Yii::$app->request->post('redactor');
        $iAthID = Yii::$app->request->post('author_id');

        $obDlgMng = new DialogManager();
        $obDialog = $obDlgMng->addNewDialogForCompany($iCmpID,$sMsg,$iAthID);
        $uniqStr = uniqid();
        $arRedisDialog = RedisNotification::getDialogListForUser(Yii::$app->user->id);
        return [
            'content' => $this->renderPartial('@common/components/widgets/liveFeed/views/_dialog_crm_msg.php',[
                'models' => [$obDialog],
                'pag' => NULL,
                'uniqStr' => $uniqStr,
                'arRedisDialog' => $arRedisDialog

            ]),
            'uniqueStr' => $uniqStr
        ];
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAddNewDialogContact()
    {
        $iCmpID = Yii::$app->request->post('cnt_id');
        $sMsg = Yii::$app->request->post('redactor');
        $iAthID = Yii::$app->request->post('author_id');

        $obDlgMng = new DialogManager();
        $obDialog = $obDlgMng->addNewDialogForContact($iCmpID,$sMsg,$iAthID);
        $uniqStr = uniqid();
        $arRedisDialog = RedisNotification::getDialogListForUser(Yii::$app->user->id);
        return [
            'content' => $this->renderPartial('@common/components/widgets/liveFeed/views/_dialog_crm_msg.php',[
                'models' => [$obDialog],
                'pag' => NULL,
                'uniqStr' => $uniqStr,
                'arRedisDialog' => $arRedisDialog

            ]),
            'uniqueStr' => $uniqStr
        ];
    }

    /**
     * @return array
     */
    public function actionLoadCmpDialogs()
    {
        $obDialogs = (new DialogManager())->getDialogsForCompany(Yii::$app->request->get('id'));
        $uniqStr = uniqid();
        $arRedisDialog = RedisNotification::getDialogListForUser(Yii::$app->user->id);
        return [
            'content' => $this->renderPartial('@common/components/widgets/liveFeed/views/_dialog_crm_msg.php',[
                'models' => $obDialogs->getModels(),
                'pag' => $obDialogs->getPagination(),
                'uniqStr' => $uniqStr,
                'arRedisDialog' => $arRedisDialog
            ]),
            'uniqueStr' => $uniqStr
        ];
    }

    /**
     * @return array
     */
    public function actionLoadContactDialogs()
    {
        $obDialogs = (new DialogManager())->getDialogsForContact(Yii::$app->request->get('id'));
        $uniqStr = uniqid();
        $arRedisDialog = RedisNotification::getDialogListForUser(Yii::$app->user->id);
        return [
            'content' => $this->renderPartial('@common/components/widgets/liveFeed/views/_dialog_crm_msg.php',[
                'models' => $obDialogs->getModels(),
                'pag' => $obDialogs->getPagination(),
                'uniqStr' => $uniqStr,
                'arRedisDialog' => $arRedisDialog
            ]),
            'uniqueStr' => $uniqStr
        ];

    }


    /**
     * @return array
     */
    public function actionLoadDialogComments()
    {
        $dID = Yii::$app->request->post('dID');
        $obComm = (new DialogManager())->getCommentsForDialog($dID);
        $type = Yii::$app->request->post('type');
        $addConf = [];
        if($type == 'task')
            $addConf['disableClick'] = TRUE;

        return [
            'content' => trim($this->renderPartial('@common/components/widgets/liveFeed/views/_dialogs_crm_comment.php',ArrayHelper::merge([
                'models' => array_reverse($obComm->getModels()),
                'pag' => $obComm->getPagination(),
                'dID' => $dID
            ],$addConf)))
        ];
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionAddCrmMsg()
    {
        $iAuthID = Yii::$app->request->post('author_id');
        $iDialogID = Yii::$app->request->post('dialog_id');
        $sMsg = trim(Yii::$app->request->post('redactor'));
        /** @var Dialogs $obDialog */
        $obDialog = Dialogs::findOne($iDialogID);
        if(!$obDialog)
            throw new NotFoundHttpException('Dialog not found');

        $obMsg = new Messages();
        $obMsg->buser_id = $iAuthID;
        $obMsg->msg = $sMsg;
        $obMsg->dialog_id = $iDialogID;
        $obMsg->status = Messages::PUBLISHED;
        $obMsg->parent_id = 0;
        $obMsg->lvl = 0;
        if(!$obMsg->save())
            throw new ServerErrorHttpException();

        $obDialog->updateUpdatedAt();
        if(!empty($obDialog->crm_task_id))
            CrmTask::updateUpdatedAtById($obDialog->crm_task_id);

        return [
            'content' => trim($this->renderPartial('@common/components/widgets/liveFeed/views/_dialogs_crm_comment.php',[
                'models' => [$obMsg],
                'pag' => NULL,
                'dID' => $iDialogID
            ]))
        ];

        return $_POST;

    }

    public function actionViewedDialog()
    {
        $iDialogID = Yii::$app->request->post('dialog_id');
        /** @var Dialogs $obDialog */
        $obDialog = Dialogs::findOne($iDialogID);
        if(!$obDialog)
            throw new NotFoundHttpException();
        $obDialog->callViewedEvent();
        return 1;
    }

    /**
     * @return bool
     */
    public function actionFlushNotification()
    {
        return RedisNotification::flushAllForUser(Yii::$app->user->id);
    }

    /**
     * @return string
     */
    public function actionLoadExchangeRates()
    {
        $arRates = ExchangeRatesManager::getCurrencyForWidget();
        $maxUpdate = NULL;
        foreach($arRates as $rate)
        {
            if(is_null($maxUpdate))
                $maxUpdate = $rate->updated_at;
            else
                $maxUpdate = $maxUpdate > $rate->updated_at ? $maxUpdate : $rate->updated_at;
        }
        return $this->renderPartial('load_exchange_rates',[
            'arRates' => $arRates,
            'maxUpdate' => $maxUpdate
        ]);
    }

    /**
     * @return false|int
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDeleteComment()
    {
        $pk = Yii::$app->request->post('pk');
        /** @var Messages $obMsg */
        $obMsg = Messages::findOne($pk);
        if(!$obMsg)
            throw new NotFoundHttpException();
        return $obMsg->delete();
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionUpdateComment()
    {
        $pk = Yii::$app->request->get('pk');
        /** @var Messages $obMsg */
        $obMsg = Messages::findOne($pk);
        if(!$obMsg)
            throw new NotFoundHttpException();

        if($obMsg->load(Yii::$app->request->post()) )
        {
            if($obMsg->save())
                return [
                    'type' => 'upd',
                    'status' => 'ok',
                    'msg' => $obMsg->msg
                ];
            else
                return [
                    'type' => 'upd',
                    'status' => 'error'
                ];
        }

        return ['type'=> 'form','body' => $this->renderPartial('update_comment',[
            'model' => $obMsg
        ])];
    }

    /**
     * @return float
     * @throws NotFoundHttpException
     */
    public function actionPartnerGetPurse()
    {
        $pk = Yii::$app->request->post('pk');
        $date = Yii::$app->request->post('date');
        $currID = Yii::$app->request->post('currID');

        if(empty($pk) || empty($date) || empty($currID))
            throw new NotFoundHttpException();

        $obPurse = PartnerPurse::getPurse($pk);
        if(!$obPurse)
            throw new NotFoundHttpException();

        $amount = (float)$obPurse->getAvailableAmount();
        $curr = ExchangeCurrencyHistory::getCurrencyInBURForDate(strtotime($date),$currID);

        return Yii::$app->formatter->asDecimal($amount/$curr);
    }
} 