<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.1.16
 * Time: 10.06
 */

namespace common\components\behavior\Company;


use common\models\Dialogs;
use common\models\Messages;
use common\models\PartnerCuserServ;
use yii\base\Behavior;
use common\models\AbstractActiveRecord;
use common\models\CUser;
use Yii;
use common\models\BuserToDialogs;
use yii\helpers\Html;

class CompanyActionBehaviors extends Behavior
{
	public
		$alreadyChecked = FALSE,
		$archivePartner = FALSE,
		$arChangedFields = [],
		$arCheckField = [
			'archive' => 'getArchiveStr',
			'manager_id' => 'manager',
			'type' => 'type'
		];
	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			AbstractActiveRecord::EVENT_SAVE_DONE => 'afterInsert',
			AbstractActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			AbstractActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
			CUser::EVENT_BEFORE_LINK => 'beforeLink'
		];
	}

	/**
	 *
	 */
	public function beforeLink()
	{
		$this->alreadyChecked = TRUE;
	}

	/**
	 * @throws \yii\db\Exception
	 */
	public function afterInsert()
	{
		/** @var Cuser $model */
		$model = $this->owner;
		$obDialog = new Dialogs();
		$obDialog->type = Dialogs::TYPE_COMPANY;
		$obDialog->buser_id = $model->created_by;
		$obDialog->crm_cmp_id = $model->id;
		$obDialog->theme = \Yii::t('app/crm','New company {cmp_name}',[
		'cmp_name' => Html::a($model->getInfo(),['/crm/company/view','id' => $model->id])
		]);
		$obDialog->status = Dialogs::PUBLISHED;

		if($obDialog->save())
		{
			$arUsers = [$model->manager_id,$model->created_by];
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

	}

	/**
	 * @return bool
	 */
	public function beforeUpdate()
	{
		if($this->alreadyChecked)
			return TRUE;
		$oldValue = $this->owner->getOldAttributes();
		$obOld = new CUser($oldValue);
		$model = $this->owner;

		foreach($this->arCheckField as $field => $method)
		{
			if($model->isAttributeChanged($field))
			{
				if($field == 'manager_id')
				{
					if($oldValue['manager_id'] != $model->manager_id)
					{
						$oldValueStr = is_object($obMan = $obOld->manager) ? $obMan->getFio() : $obOld->manager_id;
						$newValueStr = is_object($obManNew = $model->manager) ? $obManNew->getFio() : $model->manager_id;
						$this->arChangedFields [] = \Yii::t('app/msg','Field {field} from {oldValue} to {newValue}',[
							'field' => $model->getAttributelabel($field),
							'oldValue' => $oldValueStr,
							'newValue' => $newValueStr
						]);
					}
				}elseif($field == 'type')
				{
					if($oldValue['type'] != $model->type)
					{
						$oldValueStr = is_object($obType = $obOld->userType) ? $obType->name : $obOld->type;
						$newValueStr = is_object($obTypeNew = $model->userType) ? $obTypeNew->name : $model->type;
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
		//архивация партнера
		if($obOld->partner == AbstractActiveRecord::YES && $model->partner != AbstractActiveRecord::YES)
			$this->archivePartner = TRUE;
		//архивация все компании, нужно заархивировать и партнера
		if(!$this->archivePartner && $obOld->archive != AbstractActiveRecord::YES && $model->archive == AbstractActiveRecord::YES)
		{
			if(empty($this->owner->partner_archive_date))
			{
				$this->owner->partner_archive_date = Yii::$app->formatter->asDate('NOW');
			}
			$this->archivePartner = TRUE;
		}

		return TRUE;
	}

	/**
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public function afterUpdate()
	{
		if($this->alreadyChecked)
			return TRUE;
		if(!empty($this->arChangedFields))
		{
			/** @var Cuser $model */
			$model = $this->owner;
			$obDialog = new Dialogs();
			$obDialog->type = Dialogs::TYPE_COMPANY;
			$obDialog->buser_id = Yii::$app->user->id;
			$obDialog->crm_cmp_id = $model->id;
			$obDialog->theme = \Yii::t('app/crm','Changed fields for {cmp_name}',[
				'cmp_name' => Html::a($model->getInfo(),['/crm/company/view','id' => $model->id])
			]);
			$obDialog->status = Dialogs::PUBLISHED;

			if($obDialog->save())
			{
				$arUsers = [$model->manager_id,$model->created_by,Yii::$app->user->id];
				$arUsers = array_unique($arUsers);
				$rows = [];
				foreach ($arUsers as $id) {
					if(!empty($id) && $id != 0)
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
				$obMessage->msg = Yii::t('app/crm','User {user} make change fields',[
						'user' => Yii::$app->user->identity->getFio()
					]).' <br>'.implode(' ,<br>',$this->arChangedFields);
				$obMessage->dialog_id = $obDialog->id;
				$obMessage->status = Messages::PUBLISHED;
				$obMessage->save();
			}
		}
		$this->alreadyChecked = TRUE;

		if($this->archivePartner)	//архивация партнера
		{
			$arLeads = PartnerCuserServ::find()
				->where(['partner_id' => $model->id])
				->andWhere('archive is NULL OR archive = 0')
				->all();
			/** @var PartnerCuserServ $lead */
			foreach ($arLeads as $lead)
			{
				$lead->archiveDate = $model->partner_archive_date;
				$lead->archive();
			}
		}
		return TRUE;
	}


}