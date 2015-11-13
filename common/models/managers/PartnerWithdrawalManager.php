<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.11.15
 * Time: 12.41
 */

namespace common\models\managers;


use common\models\PartnerWithdrawal;
use yii\caching\TagDependency;
use common\components\helpers\CustomHelper;

class PartnerWithdrawalManager extends PartnerWithdrawal
{
	/**
	 * @param $partnerID
	 * @param null $start
	 * @param null $end
	 * @return mixed
	 * @throws \Exception
	 * Получаем сумму выведеную за период времени
	 */
	public static function getAmountByPeriod($partnerID,$start = NULL,$end = NULL)
	{
		if(is_null($start))
			$start = CustomHelper::getBeginMonthTime();

		$end =  CustomHelper::getEndDayTime(is_null($end) ? time() : $end); //чтобы каждый раз не сбрасывать кеш при подстановке end. ,будем смотреть по концу дня
		$obDep = new TagDependency([
			'tags' => self::getTagName('partner_id',$partnerID)
		]);

		return self::getDb()->cache(function($db) use ($partnerID,$start,$end){
			return self::find()
				->select('SUM(amount) as amount')
				->where(['partner_id' => $partnerID])
				->andWhere(
					'created_at >= :start AND created_at <= :end',
					[
						':start' => $start,
						':end' => $end
					]
				)
				->scalar();
		},86400,$obDep);
	}
}