<?php

namespace common\models;

use common\components\customComponents\validation\ValidNumber;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%bonus_scheme_service}}".
 *
 * @property integer $id
 * @property integer $scheme_id
 * @property integer $service_id
 * @property string $month_percent
 * @property string $cost
 * @property integer $unit_multiple
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $legal_person
 * @property number $simple_percent
 *
 * @property Services $service
 * @property BonusScheme $scheme
 */
class BonusSchemeService extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public $dublicateLastMonth;
    public static function tableName()
    {
        return '{{%bonus_scheme_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['cost',ValidNumber::className()],
            [[
                'scheme_id', 'service_id', 'unit_multiple',
                'created_at', 'updated_at'
            ], 'integer'],
            [['month_percent','legal_person'], 'string'],
            ['cost','number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['simple_percent'], 'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'scheme_id' => Yii::t('app/users', 'Scheme ID'),
            'service_id' => Yii::t('app/users', 'Service ID'),
            'month_percent' => Yii::t('app/bonus', 'Month Percent'),
            'legal_person' => Yii::t('app/users', 'Legal person'),
            'cost' => Yii::t('app/users', 'Cost'),
            'unit_multiple' => Yii::t('app/users', 'Unit Multiple'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'simple_percent' => Yii::t('app/bonus','Simple percent')
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
    public function getScheme()
    {
        return $this->hasOne(BonusScheme::className(), ['id' => 'scheme_id']);
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if(is_array($this->month_percent))
            $this->month_percent = Json::encode($this->month_percent);

        if(is_array($this->legal_person))
            $this->legal_person = Json::encode($this->legal_person);

        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(is_array($this->month_percent))
            $this->month_percent = Json::encode($this->month_percent);

        if(is_array($this->legal_person))
            $this->legal_person = Json::encode($this->legal_person);

        return parent::beforeSave($insert);
    }

    /**
     *
     */

    public function afterFind()
    {
        if(!is_array($this->month_percent) && !empty($this->month_percent)) {
            $monthPercent = Json::decode($this->month_percent);
            if(isset($monthPercent['dublicateLastMonth'])){
                $this->dublicateLastMonth = 1;
                unset($monthPercent['dublicateLastMonth']);
            }
            $this->month_percent = $monthPercent;
        }

        if(!is_array($this->legal_person) && !empty($this->legal_person))
            $this->legal_person = Json::decode($this->legal_person);

        return parent::afterFind();
    }


}
