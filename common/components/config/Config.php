<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.10.15
 * Time: 14.40
 */

namespace common\components\config;


use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class Config extends Component
{
	/**
	 * получаем все настройки
	 * @return array
	 */
	protected function getAllData()
	{
		return ArrayHelper::map(\common\models\Config::getAll(),'alias','value');
	}

	/**
	 * @param $key
	 * @param null $defaultValue
	 * @return null
	 */
	public function get($key,$defaultValue = NULL)
	{
		$tmp = $this->getAllData();
		return isset($tmp[$key]) ? $tmp[$key] : $defaultValue;
	}

	/**
	 * @param $key
	 * @param $newValue
	 * @return bool
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 */
	public function update($key,$newValue)
	{
		$obConf = \common\models\Config::find()->where(['alias' => $key])->one();
		if(empty($obConf))
			throw new NotFoundHttpException();

		$obConf->value = $newValue;
		if(!$obConf->save())
			throw new ServerErrorHttpException();

		return TRUE;
	}

}