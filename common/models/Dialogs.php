<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use backend\models\BUser;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%dialogs}}".
 *
 * @property integer $id
 * @property integer $buser_id
 * @property integer $status
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $theme
 *
 * @property BuserToDialogs[] $buserToDialogs
 * @property BUser[] $busers
 * @property BUser $buser
 * @property Messages[] $messages
 */
class Dialogs extends AbstractActiveRecord
{

    CONST
        ROW_LIMIT = 10,
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
    public function getTagClass()
    {
        $arTmp = [
            self::TYPE_MSG => 'green_tag',
            self::TYPE_REQUEST => 'red_tag'
        ];
        return array_key_exists($this->type,$arTmp) ? $arTmp[$this->type] : 'N/A';
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
            [['buser_id', 'status', 'type', 'created_at', 'updated_at'], 'integer'],
            [['theme'],'string']
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
            'theme' => Yii::t('app/dialogs', 'Theme'),
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
     * @return int
     */
    public function countDialogMessages()
    {
        $tmp = Messages::find()->where(['dialog_id' => $this->id])->count();
        return (int)$tmp;
    }

    /**
     * @param $userID
     * @param int $rowLimit
     * @return mixed
     */
    public static function getDialogsForLive($userID,$rowLimit = self::ROW_LIMIT )
    {
        $obDep = new TagDependency([
            'tags' => [
                NamingHelper::getCommonTag(self::className()),
                NamingHelper::getCommonTag(BuserToDialogs::className())
            ]
        ]);

        $arDlg = self::getDb()->cache(function($db) use ($userID,$rowLimit){
            return Dialogs::find()
                ->joinWith('busers')
                ->with([
                      'busers' => function ($query) use ($userID)  {
                             $query->andWhere(BUser::tableName().'.id is NULL OR '.
                                 BUser::tableName().'.id = '.$userID
                             );
                         }
                  ])
                ->where([self::tableName().'.status' => self::PUBLISHED])
                ->orWhere([Dialogs::tableName().'.buser_id' => $userID])
                ->limit($rowLimit)
                ->groupBy(Dialogs::tableName().'.id ')
                ->orderBy('id DESC')
                ->all();
        },86400,$obDep);
        return $arDlg;
    }
}

/**
 * Класс для работы с запросами
 * Тут добавляем scopes
 * Class DialogsQuery
 * @package common\models
 */
class DialogsQuery extends ActiveQuery
{
    public function active($state = Dialogs::PUBLISHED)
    {
        return $this->andWhere(['status' => $state]);
    }
}