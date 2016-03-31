<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bonus_scheme_except_cuser}}".
 *
 * @property integer $cuser_id
 * @property integer $scheme_id
 *
 * @property BonusScheme $scheme
 * @property CUser $cuser
 */
class BonusSchemeExceptCuser extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bonus_scheme_except_cuser}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'scheme_id'], 'required'],
            [['cuser_id', 'scheme_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cuser_id' => Yii::t('app/bonus', 'Cuser ID'),
            'scheme_id' => Yii::t('app/bonus', 'Scheme ID'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }
}
