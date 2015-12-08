<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 23.11.15
 * Time: 10.47
 */

namespace common\components\entityFields;


use common\models\EntityFields;
use yii\helpers\StringHelper;

trait EntityFieldsTrait
{
	public
		$entityFiledsValue = [],
		$entityFields = [];

	/**
	 * @return mixed
	 */
	public function getEntityFields()
	{
		$shortName = StringHelper::basename(get_class($this));
		return $this->obFields = EntityFields::getEntityFieldsForModel($shortName);
	}


	public function getEntityFieldsValue()
	{
		if(!$this->entityFields)
			$this->getEntityFields();
		if(!$this->entityFields)
			return NULL;

	}

	/**
	 * Получение дополнительных парaметров
	 * @param $valName
	 * @return null
	 */
	public function getEFVal($valName)
	{
		return isset($this->entityFiledsValue[$valName]) ? $this->entityFiledsValue[$valName]  : NULL;
	}



	public function loadWithEntityFields()
	{



	}

	protected function validateEntityFields()
	{

	}
}