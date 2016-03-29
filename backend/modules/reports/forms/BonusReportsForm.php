<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.3.16
 * Time: 10.20
 */

namespace backend\modules\reports\forms;


use common\models\BUserBonus;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class BonusReportsForm extends Model
{
	public
		$beginDate,
		$endDate,
		$users;

	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			['users','required'],
			[['beginDate','endDate'],'required'],
			['users','each','rule' => ['integer']],
			[['beginDate','endDate'],'date','format' => 'php:d.m.Y'],
			[['beginDate','endDate'],'customValidate'],
		];
	}

	public function customValidate($attribute,$params)
	{
		if(strtotime($this->beginDate) > strtotime($this->endDate))
			$this->addError($attribute,\Yii::t('app/bonus','End date must be more than begin date'));
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return [
			'beginDate' => \Yii::t('app/bonus','Begin date'),
			'endDate' => \Yii::t('app/bonus','End date'),
			'users' => \Yii::t('app/bonus','Users')
		];
	}

	/**
	 * @return array
	 */
	public function makeRequest()
	{
		$query = BUserBonus::find()
			->joinWith('service')
			->where([BUserBonus::tableName().'.buser_id' => $this->users])
			->andWhere(BUserBonus::tableName().'.updated_at >= :beginDate AND '.BUserBonus::tableName().'.updated_at <= :endDate')
			->params([
				':beginDate' => strtotime($this->beginDate.' 00:00:00'),
				':endDate' => strtotime($this->endDate.' 23:59:59')
			]);

		return [
			'dataProvider' => new ActiveDataProvider([
					'query' => $query
				]),
			'totalCount' => $query->sum('amount')
		];
	}




}