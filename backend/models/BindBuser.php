<?php

namespace app\models;

use backend\models\BUser;
use Yii;

/**
 * This is the model class for table "{{%bind_buser}}".
 *
 * @property integer $buser_id
 * @property integer $member_id
 *
 * @property BUser $member
 * @property BUser $buser
 */
class BindBuser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bind_buser}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id', 'member_id'], 'required'],
            [['buser_id', 'member_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'buser_id' => Yii::t('app/users', 'Buser ID'),
            'member_id' => Yii::t('app/users', 'Member ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(BUser::className(), ['id' => 'member_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }
}
