<?php

namespace common\models;

use Yii;
use backend\models\BUser;
use common\components\customComponents\validation\ValidNumber;
/**
 * This is the model class for table "{{%enrolls}}".
 *
 * @property integer $id
 * @property string $amount
 * @property string $repay
 * @property string $enroll
 * @property integer $enr_req_id
 * @property integer $service_id
 * @property integer $cuser_id
 * @property integer $buser_id
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Services $service
 * @property BUser $buser
 * @property CUser $cuser
 * @property EnrollmentRequest $enrReq
 */
class Enrolls extends AbstractActiveRecord
{
    public  $payName,
            $rateName,
            $rate_nbrb;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%enrolls}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enroll','repay'],ValidNumber::className()],
            //[['amount', 'repay', 'enroll'], 'number','min'=>0],
            [['enr_req_id', 'service_id', 'cuser_id', 'buser_id', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string', 'max' => 255],

            [['enroll','repay'], 'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/','min'=>0],
    //        ['enroll','validateAmount']
        ];
    }

    public function validateAmount($attribute,$param)
    {
        if(($this->enroll + $this->repay) > $this->amount)
            $this->addError($attribute,\Yii::t('app/book','Summ of Enroll and repay must be less or equal available amount'));
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'amount' => Yii::t('app/book', 'Unit amount'),
            'repay' => Yii::t('app/book', 'Repay'),
            'enroll' => Yii::t('app/book', 'Enroll'),
            'enr_req_id' => Yii::t('app/book', 'Enr Req ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'buser_id' => Yii::t('app/book', 'Buser ID'),
            'description' => Yii::t('app/book', 'Description'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
            'enroll_unit_id' =>  Yii::t('app/services','Unit enrollment'),
            'payName' =>  Yii::t('app/services','Payment condition'),
            'rateName' =>  Yii::t('app/services','Rate name'),
            'rate_nbrb' =>  Yii::t('app/services','Nbrb Rate'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    public function getUnitEnroll()
    {
        return $this->hasOne(UnitsEnroll::className(), ['id' => 'enroll_unit_id']);
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
    public function getEnrReq()
    {
        return $this->hasOne(EnrollmentRequest::className(), ['id' => 'enr_req_id']);
    }
}
