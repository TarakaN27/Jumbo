<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%files_to_messages}}".
 *
 * @property integer $file_id
 * @property integer $message_id
 *
 * @property Messages $message
 * @property Files $file
 */
class FilesToMessages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%files_to_messages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_id', 'message_id'], 'required'],
            [['file_id', 'message_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => Yii::t('app/dialogs', 'File ID'),
            'message_id' => Yii::t('app/dialogs', 'Message ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(Messages::className(), ['id' => 'message_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(Files::className(), ['id' => 'file_id']);
    }
}
