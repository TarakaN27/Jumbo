<?php

namespace common\models;

use common\components\helpers\CustomDateHelper;
use Yii;

/**
 * This is the model class for table "{{%bonus_scheme_records_history}}".
 *
 * @property integer $id
 * @property integer $scheme_id
 * @property string $params
 * @property string $update_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BonusScheme $scheme
 */
class BonusSchemeRecordsHistory extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_scheme_records_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scheme_id'], 'required'],
            [['scheme_id', 'created_at', 'updated_at'], 'integer'],
            [['params'], 'string'],
            [['update_date'], 'safe'],
            [['scheme_id'], 'exist', 'skipOnError' => true, 'targetClass' => BonusScheme::className(), 'targetAttribute' => ['scheme_id' => 'id']],
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
            'params' => Yii::t('app/users', 'Params'),
            'update_date' => Yii::t('app/users', 'Update Date'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheme()
    {
        return $this->hasOne(BonusScheme::className(), ['id' => 'scheme_id']);
    }

    /**
     * Получаем параметры на указзаную дату
     * @param $iSchemeId
     * @param $date
     * @return mixed
     */
    public static function getCurrentParamForDate($iSchemeId,$date)
    {
        if(CustomDateHelper::isCurrentMonth($date))
        {
            return BonusSchemeRecords::find()->where(['scheme_id' => $iSchemeId])->one();
        }else{
            $obParams = self::find()
                ->where(['scheme_id' => $iSchemeId])
                ->andWhere(['<=','update_date',date('Y-m-d')])
                ->one();
            if(!$obParams)
            {
                $obParams = BonusSchemeRecords::find()->where(['scheme_id' => $iSchemeId])->one();
            }
            return $obParams;
        }
    }
}
