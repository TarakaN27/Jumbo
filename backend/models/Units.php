<?php

namespace app\models;

use common\components\helpers\CustomHelper;
use common\components\loggingUserBehavior\LogModelBehavior;
use common\models\AbstractActiveRecord;
use common\models\ExchangeRates;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use common\models\CUser;
use common\models\Services;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%units}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property integer $service_id
 * @property integer $cost
 * @property integer $cuser_id
 * @property integer $multiple
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser $cuser
 * @property Services $service
 * @property UnitsCostHistory[] $unitsCostHistories
 * @property UnitsToManager[] $unitsToManagers
 */
class Units extends AbstractActiveRecord
{

    CONST
        TYPE_PAYMENT = 5;

    protected
        $_oldModelAttribute = [];

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_PAYMENT => Yii::t('app/units','Type payment')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeArr();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%units}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'cost'], 'required'],
            [['type', 'service_id', 'cost', 'cuser_id', 'multiple', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique','targetClass' => self::className()],
            ['type', 'in', 'range' => array_keys(self::getTypeArr())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/units', 'ID'),
            'name' => Yii::t('app/units', 'Name'),
            'type' => Yii::t('app/units', 'Type'),
            'service_id' => Yii::t('app/units', 'Service ID'),
            'cost' => Yii::t('app/units', 'Cost'),
            'cuser_id' => Yii::t('app/units', 'Cuser ID'),
            'multiple' => Yii::t('app/units', 'Multiple'),
            'created_at' => Yii::t('app/units', 'Created At'),
            'updated_at' => Yii::t('app/units', 'Updated At'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $arBhvrs = parent::behaviors();
        return ArrayHelper::merge(
            $arBhvrs,
            [
                [
                    'class' => LogModelBehavior::className(),
                    'ignored' => ['created_at','updated_at']
                ],
                [
                    'class' => ActiveRecordHelper::className(),
                    'cache' => 'cache', // optional option - application id of cache component
                ]
            ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnitsCostHistories()
    {
        return $this->hasMany(UnitsCostHistory::className(), ['unit_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnitsToManagers()
    {
        return $this->hasMany(UnitsToManager::className(), ['unit_id' => 'id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            $this->_oldModelAttribute = $this->oldAttributes;
            return TRUE;
        }
        return FALSE;
    }


    /**
     * @return bool
     */
    protected function saveChangedValue()
    {
        //получим старые значения аттрибутов
        $old_cost = isset($this->_oldModelAttribute['cost']) ? $this->_oldModelAttribute['cost'] : 0;

        //стоимость не изменилась
        if($old_cost == $this->cost)
            return true;

        //дата изменения
        $date = date('Y-m-d',time());

        //в один день может быть только один курс!
        /** @var UnitsCostHistory $obH */
        $obH = UnitsCostHistory::findOne(['unit_id' => $this->id,'date' => $date]);
        if(empty($obH))
            $obH = new UnitsCostHistory();

        $obH->old_cost = $old_cost;
        $obH->new_cost = $this->cost;
        if($obH->isNewRecord)
        {
            $obH->unit_id = $this->id;
            $obH->date = $date;
        }

        //сохраняем историю
        return $obH->save();
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert,$changedAttributes)
    {
        $this->saveChangedValue();
        return parent::afterSave($insert,$changedAttributes);
    }

    /**
     * @return mixed
     */
    public function getAllUnits()
    {
        $dep =  new TagDependency(['tags' => ActiveRecordHelper::getCommonTag(self::className())]);
        return self::getDb()->cache(function ($db) {
            return Units::find()->all($db);
        },86400,$dep);
    }

    /**
     * @return array
     */
    public function getUnitsMap()
    {
        $dep =  new TagDependency(['tags' => ActiveRecordHelper::getCommonTag(self::className())]);
        $models = self::getDb()->cache(function ($db) {
            return Units::find()->select(['id','name'])->all($db);
        },86400,$dep);

        return ArrayHelper::map($models,'id','name');
    }

    /**
     * @param $sDate
     * @return int|null
     */
    public function getCostForDate($sDate)
    {
        if($sDate > $this->updated_at)
            return $this->cost;
        else
        {   /** @var UnitsCostHistory $obHU */
            $obHU = UnitsCostHistory::find()
                ->where(" DATE(date) <= '".date('Y-m-d',$sDate)."'")
                ->andWhere(['unit_id' => $this->id])
                ->orderBY(' id DESC ')
                ->one();

            if(empty($obHU))
                return NULL;

            if(date('Y-m-d',$sDate) == date($obHU->date) && $obHU->old_cost != 0)
                return $obHU->old_cost;
            else
                return $obHU->new_cost;
        }
    }

}
