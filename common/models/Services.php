<?php

namespace common\models;

use backend\models\BUser;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use common\components\behavior\Service\ServiceRateBehavior;
/**
 * This is the model class for table "{{%services}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $status
 * @property number $rate
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $allow_enrollment
 * @property integer $b_user_enroll
 * @property string $enroll_unit
 */
class Services extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%services}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [[
                'status', 'created_at',
                'updated_at','b_user_enroll',
                'allow_enrollment'
            ], 'integer'],
            [['name','enroll_unit'], 'string', 'max' => 255],
            ['rate','number','min' => 100],
            [['description'], 'string', 'max' => 32],
            [['name'],'unique','targetClass' => self::className(),
             'message' => Yii::t('app/services','This name has already been taken.')],

            [['b_user_enroll','enroll_unit'],'required','when' => function($model) {
                if($model->allow_enrollment)
                    return TRUE;
                return FALSE;
            },
                'whenClient' => "function (attribute, value) {
                    if($('#services-allow_enrollment').is(':checked'))
                    {
                        return true;
                    }
                    return false;
                }"]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/services', 'ID'),
            'name' => Yii::t('app/services', 'Name'),
            'rate' => Yii::t('app/services','Rate'),
            'description' => Yii::t('app/services', 'Description'),
            'status' => Yii::t('app/services', 'Status'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
            'allow_enrollment' => Yii::t('app/services', 'Allow enrollment'),
            'b_user_enroll' => Yii::t('app/services', 'Responsibility for enrollment'),
            'enroll_unit' => Yii::t('app/services', 'Unit enrollment')
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
                ServiceRateBehavior::className()    //история изменения ставки норма часа
            ]);
    }

    /**
     * Вернем всех контрагентов
     * @return mixed
     */
    public static function getAllServices()
    {
        $dep =  new TagDependency(['tags' => NamingHelper::getCommonTag(self::className())]);
        $models = self::getDb()->cache(function ($db) {
            return Services::find()->all();
        },86400,$dep);

        return $models;
    }

    /**
     * Услуги для которых разршено зачисление
     * @return mixed
     * @throws \Exception
     */
    public static function getServiceWithAllowEnrollment()
    {
        $dep = new TagDependency([
            'tags' => NamingHelper::getCommonTag(self::className())
        ]);
        return self::getDb()->cache(function($db){
            return self::find()->where(['allow_enrollment' => self::YES])->all();
        },86400,$dep);
    }

    /**
     * вернем массив id => name
     * @return array
     */
    public static function getServicesMap()
    {
        $tmp = self::getAllServices();
        return ArrayHelper::map($tmp,'id','name');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsibilityUser()
    {
        return $this->hasOne(BUser::className(),['id' => 'b_user_enroll']);
    }

    /**
     * @return string
     */
    public function getNameWithEnrollUnit()
    {
        return $this->name.' ['.$this->enroll_unit.']';
    }
}
