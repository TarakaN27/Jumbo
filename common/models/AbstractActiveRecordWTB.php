<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use Yii;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

abstract class AbstractActiveRecordWTB extends ActiveRecord{

    use \DevGroup\TagDependencyHelper\TagDependencyTrait; //Тегированное кеширование
    CONST
        YES = 1,
        NO = 0,
        PUBLISHED = 1,
        UNPUBLISHED = 0;

    CONST
        EVENT_VIEWED = 'viewed',
        EVENT_UNLINK = 'unlink',
        EVENT_LINK = 'link';

    /**
     * @return array
     */
    public static function getYesNo()
    {
        return [
            self::NO => Yii::t('app/common','No'),
            self::YES => Yii::t('app/common','Yes'),
        ];
    }

    /**
     * @param $val
     * @return string
     */
    public function getYesNoStr($val)
    {
        $tmp = self::getYesNo();
        return isset($tmp[$val]) ? $tmp[$val] : NULL;
    }

    /**
     * Статусы записей
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::PUBLISHED => Yii::t('app/common','Published'),
            self::UNPUBLISHED => Yii::t('app/common','Unpublished')
        ];
    }

    /**
     * @return string
     */
    public function getStatusStr()
    {
        $arSts = self::getStatusArr();
        return isset($arSts[$this->status]) ? $arSts[$this->status] : 'N/A';
    }

    /**
     * Переопределим стандартный метод find,чтобы можно было ипользовать scopes.
     * Как использовать: в модели создаем класс с названием modelNameQuery()
     * в нем определяем scopes.
     * пример.
     * файл CUser.php
     * class CUser extends ...{
        ........
     * }
     * определяем класс query для модели
     * class CUserQuery extends ActiveQuery
     *   {
     *      //определяем scope для модели
     *      public function active($state = CUser::STATUS_ACTIVE)
     *      {
     *          return $this->andWhere(['status' => $state]);
     *      }
     *   }
     * использование
     * $model = CUser::find()->active()->all(); //выберем всех пользователей у которых статус = CUser::STATUS_ACTIVE
     * @return object|\yii\db\ActiveQuery
     */
    public static function find()
    {
        if(class_exists($className = self::className().'Query'))
            return Yii::createObject($className, [get_called_class()]);
        else
            return parent::find();
    }

    /**
     * Важно !
     * В классах наследниках не забывать вызывать родительский метод
     * @inheritdoc
     */
    public function behaviors()
    {
        $arTmp = parent::behaviors();
        return ArrayHelper::merge($arTmp,[
            'CacheableActiveRecord' => [
                'class' => \DevGroup\TagDependencyHelper\CacheableActiveRecord::className(),
            ],
        ]);
    }

    /**
     * Вернем кешированную запись
     * @param $model_id
     * @return mixed|null|static
     * @throws \yii\web\NotFoundHttpException
     */
    public static function findOneByIDCached($model_id,$throwExc = TRUE)
    {
        $cacheKey = get_called_class().':' . $model_id;
        if (false === $model = Yii::$app->cache->get($cacheKey)) {
            if (null === $model = self::findOne($model_id))
                if($throwExc)
                    throw new NotFoundHttpException('Model not found');
                else
                    return $model;
            Yii::$app->cache->set(
                $cacheKey,
                $model,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            NamingHelper::getObjectTag(self::className(), $model_id),
                        ]
                    ]
                )
            );
        }
        return $model;
    }

    /**
     * Для кастомного тегированного кеширования
     * @param $property
     * @param $value
     * @return string
     */
    public static function getTagName($property,$value,$className = NULL)
    {
        $className = is_null($className) ? self::getModelName(self::className()) : $className;
        return 'Tag:'.$className.':'.$property.':'.$value;
    }

    /**
     * Возвращает название класса без пути
     * @return string
     */
    public static function getModelName()
    {
        return StringHelper::basename(self::className());
    }

    /**
     * Add event LINK to link function
     * @param string $name
     * @param \yii\db\ActiveRecordInterface $model
     * @param array $extraColumns
     */
    public function link($name, $model, $extraColumns = [])
    {
        parent::link($name, $model, $extraColumns);
        $this->trigger(self::EVENT_LINK);
    }

    /**
     * Add event UNLINK to unlink function
     * @param string $name
     * @param \yii\db\ActiveRecordInterface $model
     * @param bool|FALSE $delete
     */
    public function unlink($name, $model, $delete = false)
    {
        parent::unlink($name, $model, $delete);
        $this->trigger(self::EVENT_UNLINK);
    }

    /**
     *
     */
    public function callViewedEvent()
    {
        $this->trigger(self::EVENT_VIEWED);
    }

} 