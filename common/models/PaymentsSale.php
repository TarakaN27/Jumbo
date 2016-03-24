<?php

namespace common\models;

use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%payments_sale}}".
 *
 * @property integer $id
 * @property integer $cuser_id
 * @property integer $service_id
 * @property integer $buser_id
 * @property integer $sale_date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property BUser $buser
 * @property CUser $cuser
 * @property Services $service
 */
class PaymentsSale extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%payments_sale}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cuser_id', 'service_id', 'buser_id', 'sale_date', 'created_at', 'updated_at'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'cuser_id' => Yii::t('app/book', 'Cuser ID'),
            'service_id' => Yii::t('app/book', 'Service ID'),
            'buser_id' => Yii::t('app/book', 'Buser ID'),
            'sale_date' => Yii::t('app/book', 'Sale Date'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
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
}
