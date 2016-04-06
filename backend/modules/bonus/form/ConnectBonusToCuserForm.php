<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 28.3.16
 * Time: 11.19
 */

namespace backend\modules\bonus\form;


use common\models\CUser;
use yii\base\Exception;
use yii\base\Model;

class ConnectBonusToCuserForm extends Model
{
	public
		$obScheme = NULL,
		$users = [];

	/**
	 *
	 */
	public function init()
	{
		parent::init();
		$this->initDefault();
	}

	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			['obScheme','safe'],
			['users', 'each', 'rule' => ['integer']]
		];
	}

	/**
	 * @return bool
	 */
	protected function initDefault()
	{
		$arUsers = $this->obScheme->cuserID;
		if(!empty($arUsers))
			foreach($arUsers as $user)
				$this->users [] = $user->cuser_id;

		return TRUE;
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return [
			'users' => \Yii::t('app/bonus','Cusers')
		];
	}

	/**
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public function makeRequest()
	{
		$tr = \Yii::$app->db->beginTransaction();
		try {
			$this->obScheme->unlinkAll('cusers', TRUE);
			if (!empty($this->users)) {
				$arUser = CUser::find()->where(['id' => $this->users])->all();
				foreach ($arUser as $user) {
					$this->obScheme->link('cusers', $user);
				}
			}
			$tr->commit();
			return TRUE;
		}catch (Exception $e) {
			$tr->rollBack();
			return FALSE;
		}
	}
}