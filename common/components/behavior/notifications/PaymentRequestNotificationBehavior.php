<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 4.2.16
 * Time: 10.55
 * Поведение.
 * Добавляет tabledNotification и balloon для запросов на платеж
 */

namespace common\components\behavior\notifications;


use common\components\notification\RedisNotification;
use common\components\notification\TabledNotification;
use yii\base\Behavior;
use common\models\PaymentRequest;
use Yii;
use backend\models\BUser;
use yii\helpers\Html;

class PaymentRequestNotificationBehavior extends Behavior
{

	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			PaymentRequest::EVENT_PIN_MANAGER => 'pinManager',
			PaymentRequest::EVENT_AFTER_INSERT => 'afterInsert',
			PaymentRequest::EVENT_VIEWED => 'viewed',
            PaymentRequest::EVENT_AFTER_DELETE => 'afterDelete'
		];
	}

	/**
	 * @return bool
	 */
	public function viewed()
	{
		/** @var PaymentRequest $model */
		$model = $this->owner;
		RedisNotification::removePaymentRequestFromListForUser(Yii::$app->user->id,$model->id);
		return TRUE;
	}


	/**
	 * @return bool
	 */
	public function pinManager()
	{
		/** @var PaymentRequest $model */
		$model = $this->owner;

		$arUsers = RedisNotification::getUsersToPaymentRequestList($model->id);
		if(!empty($arUsers))
		{
			RedisNotification::removePaymentRequestListForUsers($arUsers);
		}

		RedisNotification::removeAllUsersToPaymentRequest($model->id);
		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function afterInsert()
	{
		/** @var PaymentRequest $model */
		$model = $this->owner;
		$theme = Yii::t('app/crm','New payment request ');

		$cmpName = '';
		if($model->is_unknown != $model::YES && !empty($model->cntr_id))
		{
			$obCnt = $model->cuser;
			if(is_object($obCnt))
			{
				$cmpName= $obCnt->getInfo();
			}
		}else{
			$cmpName= $model->cntr_id;
		}

		$body = Html::a(
			Yii::t('app/crm','Payment request for {company}',[
			'company' => $cmpName
			]),
			['/bookkeeping/payment-request/view','id' => $model->id],
			[
				'target' => '_blank'
			]
		);

		$arUsers = [];
		if(empty($model->manager_id))
		{
			$arManagers = BUser::getManagersArr();
			if(!empty($arManagers))
				foreach($arManagers as $man)
					$arUsers [] = $man->id;
			if(!empty($arUsers))
			{
				RedisNotification::addUsersToPaymentRequestList($model->id,$arUsers);
			}
		}else{
			$arUsers [] = $model->manager_id;
		}

		if(!empty($arUsers)) {
			RedisNotification::addNewPaymentRequestToListForUsers($arUsers, $model->id);
			TabledNotification::addMessage(
				$theme,
				$body,
				TabledNotification::TYPE_PRIVATE,
				TabledNotification::NOTIF_TYPE_INFO,
				$arUsers
			);
		}
		return TRUE;
	}

    /**
     * 
     */
    public function afterDelete()
    {
        $model = $this->owner;
        if(empty($model->manager_id))
        {
            $arManagers = BUser::getManagersArr();
            if(!empty($arManagers))
                foreach($arManagers as $man)
                    $arUsers [] = $man->id;
        }else{
            $arUsers [] = $model->manager_id;
        }
        RedisNotification::removePaymentRequestFromListForUsers($arUsers,$model->id);
    }

}