<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 24.3.16
 * Time: 11.13
 */

namespace common\models\managers;


use common\models\CuserToGroup;
use common\models\Payments;
use common\models\Services;

class PaymentsManager extends Payments
{

	public static function isSale($iServID,$iCUserID)
	{
		$obServ = Services::find()->select(['id','c_inactivity'])->where(['id' => $iServID])->one();
		$arCuser = [$iCUserID];

		$tmp = CuserToGroup::find()
			->select(['group_id'])
			->where(['cuser_id' => $iCUserID])
			->all();

		if(!empty($tmp))
			foreach($tmp as $t)
				$arCuser [] = $t->cuser_id;

		$exPay = Payments::find()->where(['cuser_id',])



	}

}