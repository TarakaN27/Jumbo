<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%cuser_to_group}}".
 *
 * @property integer $cuser_id
 * @property integer $group_id
 *
 * @property CUserGroups $group
 * @property CUser $cuser
 */
class CuserToGroup extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_to_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'group_id'], 'required'],
            [['cuser_id', 'group_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cuser_id' => Yii::t('app/users', 'Cuser ID'),
            'group_id' => Yii::t('app/users', 'Group ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(CUserGroups::className(), ['id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }
}
