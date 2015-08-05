<?php

namespace common\models;

use common\components\loggingUserBehavior\LogModelBehavior;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%payment_condition}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $service_id
 * @property integer $l_person_id
 * @property integer $is_resident
 * @property string $summ_from
 * @property string $summ_to
 * @property string $corr_factor
 * @property string $commission
 * @property string $sale
 * @property string $tax
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $currency_id
 *
 * @property Services $service
 * @property LegalPerson $lPerson
 */
class PaymentCondition extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payment_condition}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                 'name', 'description', 'service_id',
                 'l_person_id', 'summ_from', 'summ_to',
                 'corr_factor', 'sale','currency_id','commission', 'tax'
             ], 'required'],
            [['name'],'unique','targetClass' => self::className(),
                'message' => Yii::t('app/book','This name has already been taken.')],
            [['description'], 'string'],
            [['service_id', 'l_person_id', 'is_resident', 'created_at', 'updated_at','currency_id'], 'integer'],
            //[['summ_from', 'summ_to', 'corr_factor', 'commission', 'sale', 'tax'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['summ_from', 'summ_to','corr_factor'],'number','min' => 0],
            [['commission', 'sale', 'tax'],'number','min' => 0],
            [['commission', 'sale', 'tax'],'number','max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'name' => Yii::t('app/book', 'Name'),
            'description' => Yii::t('app/book', 'Description'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'l_person_id' => Yii::t('app/book', 'L Person ID'),
            'is_resident' => Yii::t('app/book', 'Is Resident'),
            'summ_from' => Yii::t('app/book', 'Summ From'),
            'summ_to' => Yii::t('app/book', 'Summ To'),
            'corr_factor' => Yii::t('app/book', 'Corr Factor'),
            'commission' => Yii::t('app/book', 'Commission'),
            'sale' => Yii::t('app/book', 'Sale'),
            'tax' => Yii::t('app/book', 'Tax'),
            'currency_id' => Yii::t('app/book', 'Currency id'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
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
    public function getLPerson()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'l_person_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(),['id' => 'currency_id']);
    }


    public static function getAllCondition()
    {
        $obDep = new TagDependency([
            'tags' => [
                ActiveRecordHelper::getCommonTag(self::className()),
               // ActiveRecordHelper::getCommonTag(ExchangeRates::className())
            ]
        ]);

        return self::getDb()->cache(function($db){
           // return self::find()->with('service','currency','person')->all($db);
            return self::find()->all($db);
        },24 * 3600,$obDep);
    }

    /**
     * Получаем список условий id => название
     * @return array
     */
    public static function getConditionMap()
    {
        $arTemp = self::getAllCondition();
        return ArrayHelper::map($arTemp,'id','name');
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
                ]
            ]);
    }

}
