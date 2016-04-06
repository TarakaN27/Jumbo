<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.12.15
 * Time: 12.55
 */

namespace common\components\widgets\liveFeed;

use common\components\managers\DialogManager;
use common\components\widgets\liveFeed\assets\LiveFeedTaskAssets;
use common\models\CrmTask;
use common\models\Dialogs;
use yii\base\Widget;
use Yii;

class LiveFeedTaskWidget extends Widget
{
	public
		$iTaskID;

	public function run()
	{
		if(empty($this->iTaskID))
			return \Yii::t('app/crm','Error no dialog');
		/** @var Dialogs $obDialog */
		$obDialog = Dialogs::find()->where(['crm_task_id' => $this->iTaskID])->one();
		if(!$obDialog)
			return \Yii::t('app/crm','Error no dialog');
		$obDMan = new DialogManager();
		$arMessages = $obDMan->getCommentsForDialog($obDialog->id);
		$obDialog->callViewedEvent();
		$this->renderAssets(); //регистрируем все скрипты
		return $this->render('live_feed_task',[
			'obDialog' => $obDialog,
			'arMessages' => array_reverse($arMessages->getModels()),
			'pag' => $arMessages->getPagination(),
			'uniqStr' => 'one_task'
		]);
	}
	/**
	 *
	 */
	public function renderAssets()
	{
		$view = $this->getView();
		//тексты для js и ссылки
		$view->registerJs('
			var
				MSG_DIALOG_EMPTY_CONTENT = "'.Yii::t('app/crm','MSG_DIALOG_EMPTY_CONTENT').'",
				DIALOG_SEND_MSG_URL = "' . \yii\helpers\Url::to(['/ajax-service/add-new-dialog-contact']) . '",
			    DIALOG_LOAD_COMMENTS = "' . \yii\helpers\Url::to(['/ajax-service/load-dialog-comments']) . '",
			    DIALOG_SEND_CRM_MSG_URL = "' . \yii\helpers\Url::to(['/ajax-service/add-crm-msg']) . '",
			    DIALOG_DEL_MSG_URL = "' . \yii\helpers\Url::to(['/ajax-service/delete-comment']) . '",
			    DIALOG_UPDATE_MSG = "' . \yii\helpers\Url::to(['/ajax-service/update-comment']) . '",
			    DIALOG_ERROR_TITLE = "' . Yii::t('app/common', 'DIALOG_ERROR_TITLE') . '",
			    DIALOG_EMPTY_ID_TEXT = "' . Yii::t('app/common', 'DIALOG_EMPTY_ID_TEXT') . '",
			    DIALOG_EMPTY_ID_TEXT = "' . Yii::t('app/common', 'DIALOG_EMPTY_ID_TEXT') . '",
			    DIALOG_EMPTY_MSG_TEXT = "' . Yii::t('app/common', 'DIALOG_EMPTY_MSG_TEXT') . '",
			    DIALOG_SUCCESS_TITLE = "' . Yii::t('app/common', 'DIALOG_SUCCESS_TITLE') . '",
			    DIALOG_SUCCESS_ADD_COMMENT = "' . Yii::t('app/common', 'DIALOG_SUCCESS_ADD_COMMENT') . '",
			    DIALOG_SUCCESS_ADD_DIALOG = "' . Yii::t('app/common', 'DIALOG_SUCCESS_ADD_DIALOG') . '",
			    DIALOG_ERROR_LOAD_CONTENT = "' . Yii::t('app/common', 'DIALOG_ERROR_LOAD_CONTENT') . '",
			    DIALOG_ERROR_ADDCOMMENT = "'. Yii::t('app/common', 'DIALOG_ERROR_ADDCOMMENT') .'",
			    DIALOG_NO_COMMETS = "'. Yii::t('app/common', 'DIALOG_NO_COMMETS') .'",
			    DIALOG_ERROR_LOAD_DIALOG = "'. Yii::t('app/common', 'DIALOG_ERROR_LOAD_DIALOG') .'",
			    HIDE_MSG_TEXT = "'. Yii::t('app/common', 'HIDE_MSG_TEXT') .'",
			    SHOW_MSG_TEXT = "'. Yii::t('app/common', 'SHOW_MSG_TEXT') .'",
			    DIALOG_ERROR_ADD_MESSAGE = "'. Yii::t('app/common', 'DIALOG_ERROR_ADD_MESSAGE') .'",
			    CONFIRM_DELETE_MSG = "'. Yii::t('app/common', 'CONFIRM_DELETE_MSG') .'",
			    MESSAGE = "'. Yii::t('app/common', 'MESSAGE') .'",
			    MSG_ERROR_DEL = "'. Yii::t('app/common', 'MSG_ERROR_DEL') .'",
			    MSG_ERROR_UPDATE = "'. Yii::t('app/common', 'MSG_ERROR_UPDATE') .'",
			    DIALOG_ERROR_ADD_DIALOG = "'.Yii::t('app/crm','DIALOG_ERROR_ADD_DIALOG').'";
		',$view::POS_BEGIN);

		LiveFeedTaskAssets::register($view);
		//вешаем события
		$view->registerJs("
			$('.msg_list').on('click','.btn-load-more-comment',loadMoreComments);
			$('.form-add-msg').on('click','.addCmpMsg',addCmpMessage);
		",$view::POS_READY);
	}
}