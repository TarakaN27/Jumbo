<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 16.07.15
 */

namespace common\components\widgets\liveFeed;


use common\models\Dialogs;
use common\models\Messages;
use yii\base\Widget;
use yii\web\NotFoundHttpException;

class LiveFeedWidget extends Widget{

    public
        $userID;

    CONST
        NUMBER_OF_FEED = 10;

    public function init()
    {
        parent::init();
    }

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function run()
    {
        if(empty($this->userID))    //проверяем чтобы был указан ID пользователя
            throw new NotFoundHttpException('User ID is not defined!');

        $arDlgs = Dialogs::getDialogsForLive($this->userID,self::NUMBER_OF_FEED);    //получаем диалоги для пользователя
        $arDIDs = [];
        foreach($arDlgs as $d)
            $arDIDs[]= $d->id;

        $arMsg = Messages::getMessagesForDialogs($arDIDs);  //получаем сообщения для диалогов

        $arDialogs = [];    //собираем результирующий массив
        foreach($arDlgs as $dlg)
        {
            $tmpMsg = array_key_exists($dlg->id,$arMsg) ? $arMsg[$dlg->id] : [];

            if(!empty($tmpMsg))
            {
                $firstMsg =  $tmpMsg[0]->msg;
                unset($tmpMsg[0]);
            }
            else
                $firstMsg = 'N/A';

            $arDialogs [] = [
                'dialog' => $dlg,
                'msg' => is_array($tmpMsg) ? $tmpMsg : [],
                'firstMsg' => $firstMsg
            ];
            unset($tmpMsg);
        }

        return $this->render('life_feed',[
            'arDialogs' => $arDialogs
        ]);
    }

} 