<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.12.15
 * Time: 13.25
 */

namespace common\components\widgets\liveFeed;


use common\components\managers\DialogManager;
use common\components\widgets\liveFeed\assets\LifeFeedCompanyAssets;
use yii\base\Widget;
use Yii;

class LiveFeedCompanyWidget extends Widget
{
	public
		$iCmpID;

	public function run()
	{
		$obDialogs = (new DialogManager())->getDialogsForCompany($this->iCmpID);

		$this->renderAssets();
		return $this->render('live_feed_company',[
			'obDialogs' => $obDialogs,
			'pagination' => $obDialogs->getPagination(),
			'iCmpID' => $this->iCmpID
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
				DIALOG_SEND_MSG_URL = "' . \yii\helpers\Url::to(['/ajax-service/add-new-dialog']) . '",
			    DIALOG_LOAD_COMMENTS = "' . \yii\helpers\Url::to(['/ajax-service/load-dialog-comments']) . '",
			    DIALOG_SEND_CRM_MSG_URL = "' . \yii\helpers\Url::to(['/ajax-service/add-crm-msg']) . '",
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
			    DIALOG_ERROR_ADD_DIALOG = "'.Yii::t('app/crm','DIALOG_ERROR_ADD_DIALOG').'";
		',$view::POS_BEGIN);


		LifeFeedCompanyAssets::register($view);
		//вешаем события
		$view->registerJs("
			initDefaultState();
			$('#newDialogBtn').on('click',addNewDialogBtn);
			$('.addDialog').on('click',addNewDialog);
			$('.msgBoxList').on('click','.btn-load-more',loadMoreCmp);
			$('.msgBoxList').on('click','.btn-show-hide',showHideComments);
			$('.msg_list').on('click','.btn-load-more-comment',function(){
				loadMoreComments(false,false,this);
			});
			$('.company-msg').on('click','.addCmpMsg',addCmpMessage);
		",$view::POS_READY);
	}
}