<?php

namespace common\models;

use Yii;
use backend\models\BUser;
use yii\db\Query;

/**
 * This is the model class for table "{{%dialogs}}".
 *
 * @property integer $id
 * @property integer $buser_id
 * @property integer $status
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BuserToDialogs[] $buserToDialogs
 * @property BUser[] $busers
 * @property BUser $buser
 * @property Messages[] $messages
 */
class Dialogs extends AbstractActiveRecord
{

    CONST
        TYPE_MSG = 5,
        TYPE_REQUEST = 10;

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_MSG => Yii::t('app/common','DIALOG_message'),
            self::TYPE_REQUEST => Yii::t('app/common','DIALOG_request')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $arTmp = self::getTypeArr();
        return isset($arTmp[$this->type]) ? $arTmp[$this->type] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dialogs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id'], 'required'],
            [['buser_id', 'status', 'type', 'created_at', 'updated_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/dialogs', 'ID'),
            'buser_id' => Yii::t('app/dialogs', 'Buser ID'),
            'status' => Yii::t('app/dialogs', 'Status'),
            'type' => Yii::t('app/dialogs', 'Type'),
            'created_at' => Yii::t('app/dialogs', 'Created At'),
            'updated_at' => Yii::t('app/dialogs', 'Updated At'),
        ];
    }

    /**
     * Получаем всех участников диалога
     * @return \yii\db\ActiveQuery
     */
    public function getBusers()
    {
        return $this->hasMany(BUser::className(), ['id' => 'buser_id'])->viaTable(BuserToDialogs::tableName(), ['dialog_id' => 'id']);
    }

    /**
     * Получаем владельца диалога
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * Получаем сообщения диалога
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Messages::className(), ['dialog_id' => 'id']);
    }

    /**
     * @return int
     */
    public function countDialogMessages()
    {
        $tmp = Messages::find()->where(['dialog_id' => $this->id])->count();
        return (int)$tmp;
    }


    public function getDialogsForLive()
    {
        $query = (new Query())
            ->select('d.id,d.b')
            ->from(self::tableName().' d')
            ->leftJoin(Messages::tableName().' as m','d.id = m.dialog_id')
           ;
        return $query->createCommand()->queryAll();
    }

}
