<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.11.15
 * Time: 10.31
 */

namespace common\models\managers;


use common\components\helpers\CustomHelper;
use common\models\PartnerPurse;
use yii\caching\TagDependency;

class PartnerPurseManager extends PartnerPurse
{
	/**
	 * @param $partnerID
	 * @return bool
	 */
	public static function createPurse($partnerID)
	{
		$model = new PartnerPurse();
		$model->partner_id = $partnerID;
		$model->acts = 0;
		$model->payments = 0;
		$model->amount = 0;
		return $model->save();
	}
}