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
class CuserSettings extends \yii\db\ActiveRecord
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
            [['cuser_id', 'pp_max', 'pp_percent', 'created_at', 'updated_at'], 'integer']
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
