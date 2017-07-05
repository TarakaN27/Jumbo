<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%legal_person}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $legal_person_id
 * @property integer $status
 * @property string $bank_details
 * @property integer $updated_at
 * @property integer $created_at
 */
class BankDetails extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_details}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','bank_details', 'bill_hint'], 'string'],
            [[
                'status', 'created_at',
                'updated_at', 'legal_person_id'
            ], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name','bank_details','bank_details_act', 'legal_person_id'
            ], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/services', 'ID'),
            'name' => Yii::t('app/services', 'Name'),
            'status' => Yii::t('app/services', 'Status'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
            'legal_person_id' => Yii::t('app/services', 'Legal person'),
            'bank_details' => Yii::t('app/services','Requisites for bill'),
            'bill_hint' => Yii::t('app/services','Bill hint'),
            'bank_details_act' => Yii::t('app/services','Requisites for act'),
        ];
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
     * @return \yii\db\ActiveQuery
     */
    public function getLegalPerson()
    {
        return $this->hasOne(LegalPerson::className(),['id' => 'legal_person_id']);
    }

    public static function getActiveBankDetails()
    {
        $banks = static::findAll(['status'=>1]);
        if($banks)
            return ArrayHelper::map($banks,'id','name');
        else
            [];
    }
}
