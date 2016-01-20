<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.1.16
 * Time: 12.53
 */

namespace common\components\behavior\PaymentRequest;


use backend\models\BUser;
use common\models\AbstractActiveRecord;
use common\models\CrmTask;
use common\models\Dialogs;
use common\models\PaymentRequest;
use yii\base\Behavior;
use common\models\BuserToDialogs;
use Yii;
use yii\helpers\Html;

class PaymentRequestBehavior extends Behavior
{


	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_AFTER_INSERT => 'afterInsert'
		];
	}

	public function afterInsert()
	{
		/** @var PaymentRequest $model */
		$model = $this->owner;

		$obTask = new CrmTask();

		$obTask->assigned_id = empty($model->manager_id) ? Yii::$app->user->id : $model->manager_id;

		$theme = Yii::t('app/crm','New payment request ');

		if($model->is_unknown != $model::YES && !empty($model->cntr_id))
		{
			$obCnt = $model->cuser;
			if(is_object($obCnt))
			{
				$theme.= ' '.Html::a($obCnt->getInfo());
			}
		}else{
			$theme.= ' '.Yii::t('app/crm','Unknown contractor');
		}

		$theme = Html::a($theme,['/bookkeeping/payment-request/index']);
		$obTask->title = $theme;
		if(empty($model->manager_id))
		{
			$arManagers = BUser::getManagersArr();
			if(!empty($arManagers))
				foreach($arManagers as $man)
					$obTask->arrAcc [] = $man->id;
		}


	}

}