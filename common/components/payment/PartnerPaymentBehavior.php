<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 12.11.15
 * Time: 11.25
 */

namespace common\components\payment;


use common\components\partner\PartnerPaymentOperations;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class PartnerPaymentBehavior extends Behavior
{

	protected
		$_oldAmount = NULL,
		$_serviceID = NULL,
		$_oldUser = NULL,
		$_oldDate = NULL;


	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
		];
	}

	/**
	 *
	 */
	public function beforeUpdate()
	{
		$model = $this->owner;
		if($model->isAttributeChanged('pay_date') || $model->isAttributeChanged('cuser_id'))
			$this->_oldDate = $model->getOldAttribute('pay_date');

		if($model->isAttributeChanged('service_id') || $model->isAttributeChanged('cuser_id'))
			$this->_serviceID = $model->getOldAttribute('service_id');

		$this->_oldAmount = $model->getOldAttribute('pay_summ');

		if($model->isAttributeChanged('cuser_id'))
			$this->_oldUser = $model->getOldAttribute('cuser_id');

	}

	/**
	 * @return bool
	 */
	public function afterInsert()
	{
		$obPPO = new PartnerPaymentOperations($this->owner);
		return $obPPO->paymentInsert();
	}

	/**
	 * @return bool
	 */
	public function afterUpdate()
	{
		$obPPO = new PartnerPaymentOperations($this->owner);
		if(!is_null($this->_oldUser))
			return $obPPO->paymentUpdateUser($this->_oldDate,$this->_serviceID,$this->_oldUser,$this->_oldAmount);
		else
			return $obPPO->paymentUpdate($this->_oldAmount,$this->_oldDate,$this->_serviceID);
	}

	public function afterDelete()
	{
		$obPPO = new PartnerPaymentOperations($this->owner);
		return $obPPO->paymentDelete();
	}

}