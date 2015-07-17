<?php

namespace common\models;

use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%messages}}".
 *
 * @property integer $id
 * @property string $msg
 * @property integer $parent_id
 * @property integer $lvl
 * @property integer $buser_id
 * @property integer $dialog_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property FilesToMessages[] $filesToMessages
 * @property Files[] $files
 * @property Dialogs $dialog
 * @property BUser $buser
 */
class Messages extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%messages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['msg', 'buser_id', 'dialog_id'], 'required'],
            [['msg'], 'string'],
            [['parent_id', 'lvl', 'buser_id', 'dialog_id', 'status', 'created_at', 'updated_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/dialogs', 'ID'),
            'msg' => Yii::t('app/dialogs', 'Msg'),
            'parent_id' => Yii::t('app/dialogs', 'Parent ID'),
            'lvl' => Yii::t('app/dialogs', 'Lvl'),
            'buser_id' => Yii::t('app/dialogs', 'Buser ID'),
            'dialog_id' => Yii::t('app/dialogs', 'Dialog ID'),
            'status' => Yii::t('app/dialogs', 'Status'),
            'created_at' => Yii::t('app/dialogs', 'Created At'),
            'updated_at' => Yii::t('app/dialogs', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(Files::className(), ['id' => 'file_id'])->viaTable(FilesToMessages::tableName(), ['message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDialog()
    {
        return $this->hasOne(Dialogs::className(), ['id' => 'dialog_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }
}
