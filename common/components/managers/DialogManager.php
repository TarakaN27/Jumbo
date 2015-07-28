<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.07.15
 */

namespace common\components\managers;

use backend\models\BUser;
use common\models\Dialogs;
use common\models\Messages;
use yii\base\Component;
use yii\base\Exception;
use yii\caching\DbDependency;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

class DialogManager extends Component{

    public
        $iDId = NULL,
        $sMsg,
        $iAthID,
        $arUsers;

    /**
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public function addNewComment()
    {
        $obDlg = Dialogs::findOne($this->iDId);
        if(empty($obDlg))
            throw new NotFoundHttpException('Dialog not found');
        $arRtn = ['status' => FALSE,'content'=>'','newDialog'=>FALSE,'dialogID' => ''];

        if($obMsg = $this->newMessage($obDlg->id,$this->sMsg,$this->iAthID))
        {
            $cnt = \Yii::$app->view->renderFile(
                '@common/components/widgets/liveFeed/views/_dialog_msg.php',
                ['msg' => $obMsg ]
            );
            $arRtn = ['status' => TRUE,'content'=>$cnt,'newDialog'=>FALSE,'dialogID' => $obDlg->id];
        }

        return $arRtn;
    }

    /**
     * Добавление нового диалога
     * @return array
     */
    public function addNewDialog()
    {
        $arRtn = ['status' => FALSE,'content'=>'','newDialog'=>FALSE,'dialogID' => ''];

        $obDlg = new Dialogs();
        $obDlg->status = Dialogs::PUBLISHED;
        $obDlg->type = Dialogs::TYPE_MSG;
        $obDlg->buser_id = $this->iAthID;
        $obDlg->theme = $this->sMsg;
        $transaction = \Yii::$app->db->beginTransaction();
        if($obDlg->save())
        {
                try{
                    if(!empty($this->arUsers))
                    {
                        $arUsers = BUser::find()->where(['id' => $this->arUsers])->all();
                        foreach($arUsers as $obUser)
                        {
                            $obDlg->link('busers',$obUser);
                        }
                    }
                    $transaction->commit();

                    $arDialogs [] = ['dialog' => $obDlg,'msg' => []];

                    $arRtn = [
                        'status' => TRUE,
                        'content'=>
                            \Yii::$app->view->renderFile('@common/components/widgets/liveFeed/views/_dialog_part.php',
                                ['arDialogs' => $arDialogs ]),
                        'newDialog'=>TRUE,
                        'dialogID' => $obDlg->id];
                }catch(Exception $e)
                {
                    $transaction->rollBack();
                }
        }else{
            $transaction->rollBack();
        }

        return $arRtn;
    }

    /**
     * @param $iDId
     * @param $msgText
     * @param $iAthID
     * @param int $iPId
     * @param int $ilvl
     * @param int $sts
     * @return Messages|null
     */
    protected function newMessage($iDId,$msgText,$iAthID,$iPId=0,$ilvl=0,$sts = Messages::PUBLISHED)
    {
        $msg = new Messages();
        $msg->buser_id = $iAthID;
        $msg->dialog_id = $iDId;
        $msg->parent_id = $iPId;
        $msg->lvl = $ilvl;
        $msg->status = $sts;
        $msg->msg = $msgText;

        if($msg->save())
            return $msg;
        else
            return NULL;
    }

    /**
     * @throws \yii\web\NotFoundHttpException
     */
    public function addCommentAjaxAction()
    {
        if(is_null($this->iDId))
            throw new NotFoundHttpException('Dialog ID is empty');

        if(empty($this->sMsg))
            throw new NotFoundHttpException('Message is empty');

        if(empty($this->iAthID))
            throw new NotFoundHttpException('Author ID is empty');

        return $this->iDId == 0 ? $this->addNewDialog() : $this->addNewComment();
    }

    /**
     * @param int $page
     * @return array
     */
    public function loadDialog($page = 0)
    {
        $iDId = $this->iDId;
        //получаем сообщения для диалогов. зависимость SQL потому что при тегиррованой зависимости, слишком часто будет сбрасываться кеш.
        $obDep = new DbDependency(['sql' => 'Select MAX(updated_at) FROM '.Messages::tableName().' WHERE dialog_id  = '.$iDId]);

       return Messages::getDb()->cache(function() use ($iDId,$page){
            $query = Messages::find()->where(['dialog_id' => $this->iDId]);
            $countQuery = clone $query;
            $pages = new Pagination([
                'totalCount' => $countQuery->count(),

            ]);
            $pages->setPageSize(10);
            $pages->setPage($page);
            $models = $query->offset($pages->offset)
                ->limit($pages->limit)
                ->orderBy('id DESC ')
                ->all();

            return [
                'models' => array_reverse($models),
                'pages' =>$pages,
            ];
        },3600*24,$obDep);
    }

    public function addDialog()
    {
        $obDlg = new Dialogs();
        $obDlg->status = Dialogs::PUBLISHED;
        $obDlg->type = Dialogs::TYPE_MSG;
        $obDlg->buser_id = $this->iAthID;
        $obDlg->theme = $this->sMsg;
        $transaction = \Yii::$app->db->beginTransaction();
        if($obDlg->save())
        {
            try{
                if(!empty($this->arUsers))
                {
                    $arUsers = BUser::find()->where(['id' => $this->arUsers])->all();
                    foreach($arUsers as $obUser)
                    {
                        $obDlg->link('busers',$obUser);
                    }
                }
                $transaction->commit();
                return $obDlg;
            }catch(Exception $e)
            {
                $transaction->rollBack();
            }
        }else{
            $transaction->rollBack();
        }
        return NULL;
    }
} 