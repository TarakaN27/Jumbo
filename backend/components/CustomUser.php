<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.10.15
 * Time: 15.29
 */

namespace backend\components;


use yii\web\User;

class CustomUser extends User
{
	/**
	 * Determinate if user is manager
	 * @return bool
	 */
	public function isManager()
	{
		return $this->can('only_manager');
	}


}