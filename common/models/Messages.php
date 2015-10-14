<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use backend\models\BUser;
use yii\caching\DbDependency;
use yii\db\ActiveQuery;
use yii\db\QueryBuilder;
use yii\helpers\ArrayHelper;
use yii\swiftmailer\Message;

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
 * @todo добавить соббытие отправлено сообщение! подписаться на собьытие и сделать рассылку извещений для всплывающих оповещений
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

    /**
     * @return array
     */
    public function behaviors()
    {
        $arBhvrs = parent::behaviors();
        return ArrayHelper::merge(
            $arBhvrs,
            [

            ]);
    }

    /**
     * Получаем все сообщения для диалогов
     * @param array $arDID
     * @return mixed
     */
    public static function getMessagesForDialogs(array $arDID)
    {
        if(empty($arDID))
            return [];
        //получаем сообщения для диалогов. зависимость SQL потому что при тегиррованой зависимости, слишком часто будет сбрасываться кеш.
        $obDep = new DbDependency(['sql' => 'Select MAX(updated_at) FROM '.self::tableName().' WHERE dialog_id IN ('.implode(',',$arDID).')']);
        $arMsg = self::getDb()->cache(function($db) use ($arDID){
            return Messages::find()
                ->where(['dialog_id' => $arDID,'status' => Messages::PUBLISHED])
                ->with('buser')
                ->orderBy(self::tableName().'.id ASC')
                ->all($db);
        },86400,$obDep);

        //соберем сообщения по диалогам
        $arRst = [];
        foreach($arMsg as $msg)
            $arRst[$msg->dialog_id][] = $msg;

        return $arRst;
    }

    /**
     * Получаем сообщения для диалога
     * @param $iDId
     * @return mixed
     */
    public static function getMessagesForDialog($iDId)
    {
        //получаем сообщения для диалогов. зависимость SQL потому что при тегиррованой зависимости, слишком часто будет сбрасываться кеш.
        $obDep = new DbDependency(['sql' => 'Select MAX(updated_at) FROM '.self::tableName().' WHERE dialog_id  = '.$iDId]);
        $arMsg = self::getDb()->cache(function($db) use ($iDId){
            return Messages::find()
                ->where(['dialog_id' => $iDId,'status' => Messages::PUBLISHED])
                ->with('buser')
                ->all($db);
        },86400,$obDep);

        return $arMsg;
    }

}

/**
 * Класс для работы с запросами
 * Тут добавляем scopes
 * Class MessagesQuery
 * @package common\models
 */
class MessagesQuery extends ActiveQuery
{

    public function active($state = Messages::PUBLISHED)
    {
        return $this->andWhere(['status' => $state]);
    }
}
