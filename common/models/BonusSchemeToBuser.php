<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bonus_scheme_to_buser}}".
 *
 * @property integer $buser_id
 * @property integer $scheme_id
 *
 * @property BUser $buser
 * @property BonusScheme $scheme
 */
class BonusSchemeToBuser extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_scheme_to_buser}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id', 'scheme_id'], 'required'],
            [['buser_id', 'scheme_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'buser_id' => Yii::t('app/bonus', 'Buser ID'),
            'scheme_id' => Yii::t('app/bonus', 'Scheme ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheme()
    {
        return $this->hasOne(BonusScheme::className(), ['id' => 'scheme_id']);
    }
}
