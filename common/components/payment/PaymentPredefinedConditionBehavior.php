<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 8.2.16
 * Time: 15.09
 */

namespace common\components\payment;


use common\models\CuserPreferPayCond;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use common\models\Payments;

class PaymentPredefinedConditionBehavior extends Behavior
{

	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
		];
	}

	/**
	 * @return bool
	 */
	public function afterInsert()
	{
		/** @var Payments $model */
		$model = $this->owner;

		if(!empty($model->condition_id) && !empty($model->service_id))
		{
			if(!CuserPreferPayCond::find()->where(['cuser_id' => $model->cuser_id, 'service_id'=>$model->service_id])->exists())
			{
				$obPrefer = new CuserPreferPayCond();
				$obPrefer->service_id = $model->service_id;
				$obPrefer->cuser_id = $model->cuser_id;
				$obPrefer->cond_id = $model->condition_id;
				$obPrefer->save();
			}
		}

		return TRUE;
	}
}