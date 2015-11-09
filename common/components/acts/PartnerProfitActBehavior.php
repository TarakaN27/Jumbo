<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.11.15
 * Time: 12.50
 * Поведение для актов
 * Описываем начисление прибыли клиентам
 */

namespace common\components\acts;


use common\components\partner\PartnerProfitCounter;
use common\models\PartnerProfit;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class PartnerProfitActBehavior extends Behavior
{
	protected
		$_oldActAmount = NULL,
		$_partnerID = NULL,
		$_oldProfit = NULL;

	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
		];
	}

	/**
	 * Действие при добавлении нового акта
	 * @return bool
	 */
	public function afterInsert()
	{
		$obPPC = new PartnerProfitCounter($this->owner,TRUE);
		return $obPPC->countProfitPerPeriod();
	}

	/**
	 * Действие при обновлении акта
	 * @return bool
	 */
	public function afterUpdate()
	{
		$obPPC = new  PartnerProfitCounter($this->owner);
		return $obPPC->updateProfit($this->_oldActAmount);
	}

	/**
	 *
	 */
	public function beforeUpdate()
	{
		$this->_oldActAmount = $this->owner->getOldAttribute('amount');
	}

	/**
	 * Перед удалением сохраним старые значения прибыли партнера, если такие есть
	 */
	public function beforeDelete()
	{
		$obProfit = PartnerProfit::find()->where(['act_id' => $this->owner->id])->one();
		if($obProfit) {
			$this->_partnerID = $obProfit->partner_id;
			$this->_oldProfit = $obProfit->amount;
		}
	}

	/**
	 * Действие при удалении акта
	 * @return bool
	 */
	public function afterDelete()
	{
		$obPPC = new  PartnerProfitCounter($this->owner);
		return $obPPC->deleteProfit($this->_oldProfit,$this->_partnerID);
	}

}