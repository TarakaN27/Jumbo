<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.10.15
 * Time: 15.29
 * @property boolean cRMLevelAccess return access level for crm . This property is read-only.
 * @property boolean manager Whether the current user is a manager. This property is read-only.
 */

namespace backend\components;


use common\models\BUserCrmRules;
use yii\web\User;

class CustomUser extends User
{
	protected
		$_crmAcceess = []; //доступы к crm. Ключ -- сущность

	/**
	 * Determinate if user is manager
	 * @return bool
	 */
	public function isManager()
	{
		return $this->can('only_manager');
	}

	/**
	 * Возвращаем тип правила для действия $action c сущностью $entity.
	 * @param $entity
	 * @param $action
	 * @return int|null
	 */
	public function getCRMLevelAccess($entity,$action)
	{
		if($this->isGuest)
			return NULL;

		$arrAllowedAction = BUserCrmRules::getAllowedAction();
		if(!in_array($action,$arrAllowedAction))
			throw new \InvalidArgumentException('action is not allowed');

		if(!isset($this->_crmAcceess[$entity]))
		{
			$this->_crmAcceess[$entity] = $this->identity->getCRMRulesByGroup($entity);
		}

		if(is_object($obRule = $this->_crmAcceess[$entity]))
			return $obRule->$action;

		return BUserCrmRules::RULE_CLOSED;
	}

	/**
	 * @param $model
	 * @param string $createdField
	 * @param string $assignFiled
	 * @param string $openedField
	 * @return bool
	 */
	public function crmCanEditModel($model,$createdField = 'created_by',$assignFiled = 'manager_id',$openedField = 'opened')
	{
		$iLevel = $this->getCRMLevelAccess($model::getModelName(),BUserCrmRules::UPDATE_ACTION);
		return $this->crmLevelHelper($iLevel,$model,$createdField,$assignFiled,$openedField);
	}

	/**
	 * @param $model
	 * @param string $createdField
	 * @param string $assignFiled
	 * @param string $openedField
	 * @return bool
	 */
	public function crmCanDeleteModel($model,$createdField = 'created_by',$assignFiled = 'manager_id',$openedField = 'opened')
	{
		$iLevel = $this->getCRMLevelAccess($model::getModelName(),BUserCrmRules::DELETE_ACTION);
		return $this->crmLevelHelper($iLevel,$model,$createdField,$assignFiled,$openedField);
	}

	/**
	 * @param $iLevel
	 * @param $model
	 * @param string $createdField
	 * @param string $assignFiled
	 * @param string $openedField
	 * @return bool
	 */
	protected function crmLevelHelper($iLevel,$model,$createdField = 'created_by',$assignFiled = 'manager_id',$openedField = 'opened')
	{
		$bReturn = FALSE;
		switch($iLevel)
		{
			case BUserCrmRules::RULE_ALL:
				$bReturn = TRUE;
				break;

			case BUserCrmRules::RULE_OPENED:
				if($model->$openedField)
					$bReturn = TRUE;
				break;

			case BUserCrmRules::RULE_THEMSELF:
				if($model->$createdField == \Yii::$app->user->id)
					$bReturn = TRUE;

				if($model->$assignFiled == \Yii::$app->user->id)
					$bReturn = TRUE;

				break;
			default:
				break;
		}

		return $bReturn;
	}


}