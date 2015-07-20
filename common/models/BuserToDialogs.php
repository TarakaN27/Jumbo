<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\helpers\ArrayHelper;
use backend\models\BUser;

/**
 * This is the model class for table "{{%buser_to_dialogs}}".
 *
 * @property integer $buser_id
 * @property integer $dialog_id
 *
 * @property Dialogs $dialog
 * @property BUser $buser
 */
class BuserToDialogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%buser_to_dialogs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['buser_id', 'dialog_id'], 'required'],
            [['buser_id', 'dialog_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'buser_id' => Yii::t('app/dialogs', 'Buser ID'),
            'dialog_id' => Yii::t('app/dialogs', 'Dialog ID'),
        ];
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
                [
                    'class' => ActiveRecordHelper::className(),
                    'cache' => 'cache', // optional option - application id of cache component
                ]
            ]);
    }
}
