<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 23.3.16
 * Time: 14.50
 */

namespace backend\modules\bonus\form;


use backend\models\BUser;
use yii\base\Exception;
use yii\base\Model;

class ConnectBonusToUserForm extends Model
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
		$arUsers = $this->obScheme->usersID;
		if(!empty($arUsers))
			foreach($arUsers as $user)
				$this->users [] = $user->buser_id;

		return TRUE;
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return [
			'users' => \Yii::t('app/bonus','Users')
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
			$this->obScheme->unlinkAll('users', TRUE);
			if (!empty($this->users)) {
				$arUser = BUser::find()->where(['id' => $this->users])->all();
				foreach ($arUser as $user) {
					$this->obScheme->link('users', $user);
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