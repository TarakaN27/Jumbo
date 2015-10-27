<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%cuser_settings}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $pp_max
 * @property integer $pp_percent
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser $cuser
 */
class CuserSettings extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_settings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id'], 'required'],
            [['cuser_id', 'created_at', 'updated_at'], 'integer'],
            ['pp_percent','integer','min' => 0,'max' => 100],
            ['pp_max','integer','min'=>0]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'pp_max' => Yii::t('app/users', 'Pp Max'),
            'pp_percent' => Yii::t('app/users', 'Pp Percent'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }
}
