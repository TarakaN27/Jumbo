<?php

namespace common\models;

use common\components\helpers\CustomHelper;
use Yii;

/**
 * This is the model class for table "{{%bonus_scheme_service_history}}".
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
class BonusSchemeServiceHistory extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_scheme_service_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheme_id', 'service_id', 'unit_multiple', 'created_at', 'updated_at'], 'integer'],
            [['month_percent','legal_person'], 'string'],
            [['cost','simple_percent'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/bonus', 'ID'),
            'scheme_id' => Yii::t('app/bonus', 'Scheme ID'),
            'service_id' => Yii::t('app/bonus', 'Service ID'),
            'month_percent' => Yii::t('app/bonus', 'Month Percent'),
            'cost' => Yii::t('app/bonus', 'Cost'),
            'unit_multiple' => Yii::t('app/bonus', 'Unit Multiple'),
            'created_at' => Yii::t('app/bonus', 'Created At'),
            'updated_at' => Yii::t('app/bonus', 'Updated At'),
            'legal_person' => Yii::t('app/users', 'Legal person'),
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
     * @param $time
     * @param $iServID
     * @param $iScheme
     * @return null
     */
    public static function getCurrentBonusService($time,$iServID,$iScheme)
    {
        $obScheme = NULL;
        if(CustomHelper::isCurrentDay($time))   //это текущий день
        {
            $obScheme = BonusSchemeService::find()
                ->where(['service_id' => $iServID,'scheme_id' => $iScheme])
                ->orderBy(['updated_at' => SORT_DESC])
                ->one();
        }else{
            $obScheme = BonusSchemeServiceHistory::find()
                ->where(['service_id' => $iServID,'id' => $iScheme])
                ->orderBy(['updated_at' => SORT_DESC])
                ->one();
            if(empty($obScheme))
                $obScheme = BonusSchemeService::find()
                    ->where(['service_id' => $iServID,'id' => $iScheme])
                    ->orderBy(['updated_at' => SORT_DESC])
                    ->one();
        }

        return $obScheme;
    }
}
