<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.11.15
 * Time: 14.01
 */

namespace common\components\partner;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class PartnerLinkCuserServBehavior extends Behavior
{

	public function events()
	{
		return[
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
		];
	}


	public function afterInsert()
	{
		$model = $this->owner;
		$obPPCS = new PartnerProfitCounterShare();
		return $obPPCS->countingProfitForPartner($model->partner_id,$model->cuser_id,$model->service_id,$model->connect);
	}

	public function afterDelete()
	{
		//@todo выяснить что делать при удалении связи
	}

}