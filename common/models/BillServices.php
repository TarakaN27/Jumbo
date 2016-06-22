<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%bill_services}}".
 *
 * @property integer $id
 * @property integer $bill_id
 * @property integer $service_id
 * @property integer $serv_tpl_id
 * @property string $amount
 * @property string $serv_title
 * @property string $description
 * @property string $offer
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $ordering
 *
 * @property BillTemplate $servTpl
 * @property Bills $bill
 * @property Services $service
 */
class BillServices extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bill_services}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bill_id', 'service_id', 'amount', 'serv_title'], 'required'],
            [['bill_id', 'service_id', 'serv_tpl_id', 'created_at', 'updated_at','ordering'], 'integer'],
            [['amount'], 'number'],
            [['serv_title', 'description', 'offer'], 'string'],
            [['serv_tpl_id'], 'exist', 'skipOnError' => true, 'targetClass' => BillTemplate::className(), 'targetAttribute' => ['serv_tpl_id' => 'id']],
            [['bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bills::className(), 'targetAttribute' => ['bill_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/documents', 'ID'),
            'bill_id' => Yii::t('app/documents', 'Bill ID'),
            'service_id' => Yii::t('app/documents', 'Service ID'),
            'serv_tpl_id' => Yii::t('app/documents', 'Serv Tpl ID'),
            'amount' => Yii::t('app/documents', 'Amount'),
            'serv_title' => Yii::t('app/documents', 'Serv Title'),
            'description' => Yii::t('app/documents', 'Description'),
            'offer' => Yii::t('app/documents', 'Offer'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
            'ordering' => Yii::t('app/documents','Ordering')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServTpl()
    {
        return $this->hasOne(BillTemplate::className(), ['id' => 'serv_tpl_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bills::className(), ['id' => 'bill_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }
}
