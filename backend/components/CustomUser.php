<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.10.15
 * Time: 15.29
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


}