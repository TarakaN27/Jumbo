<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 17.2.16
 * Time: 9.45
 */

namespace common\components\behavior\Service;

use common\models\ServiceRateHist;
use common\models\Services;
use yii\base\Behavior;
use yii\web\ServerErrorHttpException;

class ServiceRateBehavior extends Behavior
{

	protected
		$rateChange = FALSE,
		$oldRate = NULL;

	/**
	 * Назначаем событиям обработчики
	 * @return array
	 */
	public function events()
	{
		return [
			Services::EVENT_BEFORE_UPDATE => 'beforeUpdate',
			Services::EVENT_AFTER_UPDATE => 'afterUpdate'
		];
	}

	/**
	 * @return bool
	 */
	public function beforeUpdate()
	{
		$this->rateChange = $this->owner->isAttributeChanged('rate');
		$this->oldRate = $this->owner->getOldAttribute('rate');
		return TRUE;
	}

	/**
	 * @return bool
	 * @throws ServerErrorHttpException
	 */
	public function afterUpdate()
	{
		if($this->rateChange)
		{
			/** @var Services $model */
			$model = $this->owner;
			$date = date('Y-m-d',time());
			$obHist = ServiceRateHist::find()->where(['date' => $date,'service_id' => $model->id])->one();
			if(!$obHist)
			{
				$obHist = new ServiceRateHist([
					'date' => $date,
					'service_id' => $model->id
				]);

			}
			if(!is_null($this->oldRate))
				$obHist->old_rate = $this->oldRate ;
			$obHist->new_rate = $model->rate;

			if(!$obHist->save())
			{
				throw new ServerErrorHttpException();
			}
		}
		return TRUE;
	}
}