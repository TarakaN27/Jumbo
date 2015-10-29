<?php

namespace common\models;

use backend\models\BUser;
use Yii;

/**
 * This is the model class for table "{{%acts}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $buser_id
 * @property integer $service_id
 * @property integer $template_id
 * @property string $amount
 * @property string $act_date
 * @property integer $sent
 * @property integer $change
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ActsTemplate $template
 * @property BUser $buser
 * @property CUser $cuser
 * @property Services $service
 */
class Acts extends AbstractActiveRecord
{

    public
        $updateFile = FALSE;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%acts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'buser_id', 'service_id', 'template_id', 'amount'], 'required'],
            [['cuser_id', 'buser_id', 'service_id', 'template_id', 'sent', 'change', 'created_at', 'updated_at'], 'integer'],
            [['act_date'], 'safe'],
            ['act_date','date', 'format' => 'yyyy-m-dd'],
            [['amount'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/documents', 'ID'),
            'cuser_id' => Yii::t('app/documents', 'Cuser ID'),
            'buser_id' => Yii::t('app/documents', 'Buser ID'),
            'service_id' => Yii::t('app/documents', 'Service ID'),
            'template_id' => Yii::t('app/documents', 'Template ID'),
            'amount' => Yii::t('app/documents', 'Amount'),
            'act_date' => Yii::t('app/documents', 'Act Date'),
            'sent' => Yii::t('app/documents', 'Sent'),
            'change' => Yii::t('app/documents', 'Change'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(ActsTemplate::className(), ['id' => 'template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if($insert)


        return parent::afterSave($insert, $changedAttributes);
    }
}
