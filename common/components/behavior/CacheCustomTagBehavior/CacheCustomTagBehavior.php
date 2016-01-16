<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.11.15
 * Time: 12.03
 *
 * Пример использования.
 * Класс модели должен быть унаследован от AbstractActiveRecordWTB или его потомков. так как в данном класе
 * описан метод получения тега. Данный класс является общим для всех моделей.
 * Далее подключаем поведение для сброса кеша по тегу
 * public function behaviors()
 * {
 *	$arBhvrs = parent::behaviors();
 *	return ArrayHelper::merge(
 *		$arBhvrs,
 *		[
 *			'class' => CacheCustomTagBehavior::className(), // указываем клас поведения
 *          'items' => ['name']    // указываем по каким полям инвалидируем кеш
 *		]);
 * }
 *
 * далее описываем метод в которм будем кешировать.
 *
 * public static function getRecordByName($name)
 * {
 *      $obDep = new TagDependency([
 *          'tags' => self::getTagName('name',$name)
 *      ]);
 *
 *      return self::getDb()->cache(function($db) use ($name){
 *			return self::find()->where(['name' => $name])->all();
 *      },86400,$obDep)
 *
 * }
 *
 * В методе getRecordByName данные будут кешироваться в зависимости от name. т.е мы создаем тег с параметром $name
 * После любого измения инвалдируются теги, параметры которых указаны в $items поведения.
 * так для того, чтобы тег в примере инвалидировался, необходимо в $item указать свойство модели 'name'
 */

namespace common\components\behavior\CacheCustomTagBehavior;


use common\models\AbstractActiveRecordWTB;
use yii\base\Behavior;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use common\models\AbstractActiveRecord;
class CacheCustomTagBehavior extends Behavior
{
	public
		$items = [];

	/**
	 * Get events list.
	 * @return array
	 */
	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_DELETE => [$this->owner, 'invalidateTags'],
			ActiveRecord::EVENT_AFTER_INSERT => [$this->owner, 'invalidateTags'],
			ActiveRecord::EVENT_AFTER_UPDATE => [$this->owner, 'invalidateTags'],
		];
	}

	/**
	 * После изменения данных инвалидируем данные.
	 * @return bool
	 */
	public function invalidateTags()
	{
		$model = $this->owner;
		$tags = [];
		foreach($this->items as $item)
		{
			if(property_exists($model,$item))
			{
				$tag []= AbstractActiveRecordWTB::getTagName($model->className(),$item,$model->$item);
			}
		}

		if(!empty($tags))
			TagDependency::invalidate(\Yii::$app->cache,$tags);

		return true;
	}

	/**
	 * @param array $arUsers
	 * @param $modelName
	 * @param $field
	 * @return bool
	 */
	public static function invalidateTagForUsers(array $arUsers,$modelName,$field)
	{
		$tags = [];
		foreach($arUsers as $user)
		{
			$tags []= AbstractActiveRecord::getTagName($field,$user,$modelName);
		}

		if(!empty($tags))
			TagDependency::invalidate(\Yii::$app->cache,$tags);
		return true;
	}
}