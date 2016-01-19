<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.1.16
 * Time: 12.37
 */

namespace common\components\behavior\Contact;


use common\models\CrmCmpContacts;
use yii\base\Behavior;
use common\models\Dialogs;
use common\models\Messages;
use common\models\AbstractActiveRecord;
use common\models\CUser;
use Yii;
use common\models\BuserToDialogs;
use yii\helpers\Html;

class ContactActionBehavior extends Behavior
{

	public
		$arChangedFields = [],
		$arCheckField = [
			'type' => 'getTypeStr',
			'assigned_at' => 'assigned_at'
		];
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			AbstractActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			AbstractActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
		];
	}

	/**
	 * @throws \yii\db\Exception
	 */
	public function afterInsert()
	{
		$model = $this->owner;
		$obDialog = new Dialogs();
		$obDialog->type = Dialogs::TYPE_COMPANY;
		$obDialog->buser_id = $model->created_by;
		$obDialog->crm_cmp_contact_id = $model->id;
		$obDialog->theme = \Yii::t('app/crm','New contact {cmp_name}',[
			'cmp_name' => Html::a($model->fio,['/crm/contact/view','id' => $model->id])
		]);
		$obDialog->status = Dialogs::PUBLISHED;

		if($obDialog->save())
		{
			$arUsers = [$model->assigned_at,$model->created_by];
			$arUsers = array_unique($arUsers);
			$rows = [];
			foreach ($arUsers as $id) {
				$rows [] = [(int)$id, $obDialog->id];
			}
			$postModel = new BuserToDialogs();
			//групповое добавление
			Yii::$app->db->createCommand()
				->batchInsert(BuserToDialogs::tableName(), $postModel->attributes(), $rows)
				->execute();
			$obDialog->callSaveDoneEvent();
		}
		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function beforeUpdate()
	{
		$oldValue = $this->owner->getOldAttributes();
		$obOld = new CrmCmpContacts($oldValue);
		$model = $this->owner;

		foreach($this->arCheckField as $field => $method)
		{
			if($model->isAttributeChanged($field))
			{
				if($field == 'assigned_at')
				{
					if($oldValue['assigned_at'] != $model->assigned_at)
					{
						$oldValueStr = is_object($obMan = $obOld->assignedAt) ? $obMan->getFio() : $obOld->assigned_at;
						$newValueStr = is_object($obManNew = $model->assignedAt) ? $obManNew->getFio() : $model->assigned_at;
						$this->arChangedFields [] = \Yii::t('app/msg','Field {field} from {oldValue} to {newValue}',[
							'field' => $model->getAttributelabel($field),
							'oldValue' => $oldValueStr,
							'newValue' => $newValueStr
						]);
					}
				}
				else{
					$oldValueStr = $method ? $obOld->$method() : $obOld->$field;
					$newValueStr = $method ? $model->$method() : $model->$field;
					if($oldValueStr != $newValueStr)
						$this->arChangedFields []= \Yii::t('app/msg','Field {field} from {oldValue} to {newValue}',[
							'field' => $this->owner->getAttributelabel($field),
							'oldValue' => $oldValueStr,
							'newValue' => $newValueStr
						]);
				}
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public function afterUpdate()
	{
		if(!empty($this->arChangedFields))
		{
			$model = $this->owner;
			$obDialog = new Dialogs();
			$obDialog->type = Dialogs::TYPE_COMPANY;
			$obDialog->buser_id = Yii::$app->user->id;
			$obDialog->crm_cmp_contact_id = $model->id;
			$obDialog->theme = \Yii::t('app/crm','Changed fields for contact {cmp_name}',[
				'cmp_name' => Html::a($model->fio,['/crm/contact/view','id' => $model->id])
			]);
			$obDialog->status = Dialogs::PUBLISHED;

			if($obDialog->save())
			{
				$arUsers = [$model->assigned_at,$model->created_by,Yii::$app->user->id];
				$arUsers = array_unique($arUsers);
				$rows = [];
				foreach ($arUsers as $id) {
					$rows [] = [$id, $obDialog->id];
				}

				$postModel = new BuserToDialogs();
				//групповое добавление
				Yii::$app->db->createCommand()
					->batchInsert(BuserToDialogs::tableName(), $postModel->attributes(), $rows)
					->execute();
				$obDialog->callSaveDoneEvent();

				$obMessage = new Messages();
				$obMessage->buser_id = Yii::$app->user->id;
				$obMessage->msg = Yii::t('app/crm','User {user} make change fields for contact',[
						'user' => Yii::$app->user->identity->getFio()
					]).' <br>'.implode(' ,<br>',$this->arChangedFields);
				$obMessage->dialog_id = $obDialog->id;
				$obMessage->status = Messages::PUBLISHED;
				$obMessage->save();
			}
		}
		return TRUE;
	}

}