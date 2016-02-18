<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%cuser_quantity_hour}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property double $hours
 * @property double $spent_time
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CUser $cuser
 */
class CuserQuantityHour extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_quantity_hour}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['cuser_id','unique'],
            [[
                'cuser_id',
                'created_at',
                'updated_at'
            ], 'integer'],
            [['hours', 'spent_time'], 'number','min' => 0 ]
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
            'hours' => Yii::t('app/users', 'Hours'),
            'spent_time' => Yii::t('app/users', 'Spent Time'),
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
