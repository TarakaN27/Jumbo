<?php

namespace common\models;

use Yii;
use backend\models\BUser;

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
            [['amount', 'repay', 'enroll'], 'number','min'=>0],
            [['enr_req_id', 'service_id', 'cuser_id', 'buser_id', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string', 'max' => 255],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
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
