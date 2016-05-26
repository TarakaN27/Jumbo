<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%act_field_template}}".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $legal_id
 * @property string $job_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property LegalPerson $legal
 * @property Services $service
 */
class ActFieldTemplate extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act_field_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'legal_id'], 'required'],
            [['service_id', 'legal_id', 'created_at', 'updated_at'], 'integer'],
            [['job_name'], 'string'],
            [['legal_id'], 'exist', 'skipOnError' => true, 'targetClass' => LegalPerson::className(), 'targetAttribute' => ['legal_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['service_id','legal_id'],'customValidate']
        ];
    }

    /**
     * @param $attribute
     * @param $param
     */
    public function customValidate($attribute,$param)
    {
        if($this->isNewRecord)
        {
            if(self::find()->where(['service_id' => $this->service_id,'legal_id' => $this->legal_id])->exists())
                $this->addError($attribute,Yii::t('app/book','This template already exist'));
        }else{
            if($this->isAttributeChanged('service_id') || $this->isAttributeRequired('legal_id'))
                if(self::find()->where(['service_id' => $this->service_id,'legal_id' => $this->legal_id])->exists())
                    $this->addError($attribute,Yii::t('app/book','This template already exist'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'legal_id' => Yii::t('app/book', 'Legal ID'),
            'job_name' => Yii::t('app/book', 'Job Name'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLegal()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'legal_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }
}
