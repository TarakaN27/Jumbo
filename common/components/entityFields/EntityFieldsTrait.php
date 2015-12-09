<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 23.11.15
 * Time: 10.47
 * Как добавить дополнительные поля к модели.
 * В класе модели нужно подключить trait
 class Acts extends AbstractActiveRecord
{
	use EntityFieldsTrait; //подключаем треит
 ..........

 в rules нужно добавить правило  [['entityFields'], 'safe'], иначе не будут сохраняться значение доп. полей
 * Доп. поля добавляются в насйтрках и задаются для каждой модели.


 *
 */
namespace common\components\entityFields;


use common\models\EntityFields;
use common\models\EntityFieldsValue;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;

trait EntityFieldsTrait
{
	public
		$entityFields = [];

	protected
		$entityShortName,
		$obFields;

	/**
	 * @return mixed
	 */
	public function getEntityFields()
	{
		if(!empty($this->obFields))
			return $this->obFields;
		$this->entityShortName = StringHelper::basename(get_class($this));
		return $this->obFields = EntityFields::getEntityFieldsForModel($this->entityShortName);
	}

	/**
	 * @return array|null
	 */
	public function getEntityFieldsValue()
	{
		if(!$this->obFields)
			$this->getEntityFields();
		if(!$this->obFields)
			return NULL;
		$arID = [];
		foreach($this->obFields as $ff)
			$arID [] = $ff->id;
		$arFieldsVal = EntityFieldsValue::getFieldsValue($arID,$this->entityShortName,$this->id);
		$arValue = [];
		foreach($arFieldsVal as $value)
		{
			$arValue [$value->field_id] = $value->value;
		}
		foreach($this->obFields as $field)
		{
			$this->entityFields[$field->alias] = isset($arValue[$field->id]) ? $arValue[$field->id] : NULL;
		}
		return $this->entityFields;
	}

	/**
	 * Получение дополнительных парaметров
	 * @param $valName
	 * @return null
	 */
	public function getEFVal($valName)
	{
		if(empty($this->entityFields))
			$this->getEntityFieldsValue();

		return isset($this->entityFileds[$valName]) ? $this->entityFileds[$valName]  : NULL;
	}



	public function loadWithEntityFields()
	{



	}

	protected function validateEntityFields()
	{


	}

	/**
	 * Вешаем обработчики событий
	 * @return mixed
	 */
	public function init()
	{
		//$this->on(ActiveRecord::EVENT_BEFORE_VALIDATE,);
		$this->on(ActiveRecord::EVENT_AFTER_INSERT,[$this,'saveNewEntityFields']);
		$this->on(ActiveRecord::EVENT_AFTER_UPDATE,[$this,'updateEntityFields']);
		$this->on(ActiveRecord::EVENT_AFTER_DELETE,[$this,'deleteEntityFieldsValue']);
		return parent::init();
	}

	/**
	 * @return bool
	 */
	public function saveNewEntityFields()
	{
		$fields = $this->obFields;
		if(empty($fields))
			return FALSE;

		/** @var EntityFields $value */
		foreach($fields as $value)
		{
			if(isset($this->entityFields[$value->alias]))
			{
				$this->createNewFieldValue($value);
			}
		}
		return TRUE;
	}

	/**
	 * @param $field
	 * @return bool
	 */
	protected function createNewFieldValue($field)
	{
		$obNewValue = new EntityFieldsValue();
		$obNewValue->entity = $this->entityShortName;
		$obNewValue->item_id = $this->id;
		$obNewValue->field_id = $field->id;
		$obNewValue->value = $this->entityFields[$field->alias];
		return $obNewValue->save();
	}

	/**
	 * @return bool
	 */
	public function updateEntityFields()
	{
		$fields = $this->obFields; //получаем доп. поля для модели
		if(empty($fields))
			return FALSE;

		$arFieldIDs = []; //собираем ID полей
		foreach($fields as $field)
			$arFieldIDs [] = $field->id;

		$oldValues = EntityFieldsValue::find()->where([
			'field_id' => $arFieldIDs,
			'item_id' => $this->id,
			'entity' => $this->entityShortName
		])->all(); //получаем старые значения полей
		$arOldVal = []; //соберем значения полей по ID полей
		foreach($oldValues as $val)
			$arOldVal[$val->field_id] = $val;
		unset($oldValues);

		foreach($fields as $value) //проходим по полям
		{
			if(!isset($this->entityFields[$value->alias])) //если в посет не было полей, продолжим обход массива
				continue;

			$newVal = $this->entityFields[$value->alias];   //новое значение

			if(isset($arOldVal[$value->id])) //если есть старое значение , проверим на актуальность
			{
				if($arOldVal[$value->id]->value != $newVal) { //значение изменилось?
					$arOldVal[$value->id]->value = $newVal;
					$arOldVal[$value->id]->save(); //сохраняем новое значение
				}
			}else{
				$this->createNewFieldValue($value); //если нет старого значения , добавим
			}
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function deleteEntityFieldsValue()
	{
		$obFileds = $this->getEntityFields(); //получаем доп. поля
		$arFieldsIDs = [];
		foreach($obFileds as $field)
			$arFieldsIDs [] = $field->id; //собираем ids полей
		unset($obFileds);
		// после удаления записи , удаляем и все доп поля.
		EntityFieldsValue::deleteAll([
			'field_id' => $arFieldsIDs,
			'item_id' => $this->id,
			'entity' => $this->entityShortName
		]);
		return TRUE;
	}

	/**
	 * Получаем доп поля с названием, alias и занчением
	 * @return array
	 */
	public function getDisplayEntityValues()
	{
		if(!$arValues = $this->getEntityFieldsValue())
			return [];
		$arFields = $this->obFields;
		$arResult = [];

		if(is_array($arFields))
			foreach($arFields as $fields)
				if(isset($arValues[$fields->alias]))
					$arResult [] = [
						'alias' => $fields->alias,
						'name' => $fields->name,
						'value' => $arValues[$fields->alias]
					];

		return $arResult;
	}

}