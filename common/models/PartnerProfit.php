<?php

namespace common\models;

use common\components\loggingUserBehavior\LogModelBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%partner_profit}}".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property integer $act_id
 * @property integer $cond_id
 * @property string $amount
 * @property double $percent
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PartnerCondition $cond
 * @property Acts $act
 * @property Partner $partner
 */
class PartnerProfit extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_profit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'act_id', 'cond_id', 'amount', 'percent'], 'required'],
            [['partner_id', 'act_id', 'cond_id', 'created_at', 'updated_at'], 'integer'],
            [['amount', 'percent'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'partner_id' => Yii::t('app/book', 'Partner ID'),
            'act_id' => Yii::t('app/book', 'Act ID'),
            'cond_id' => Yii::t('app/book', 'Cond ID'),
            'amount' => Yii::t('app/book', 'Amount'),
            'percent' => Yii::t('app/book', 'Percent'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCond()
    {
        return $this->hasOne(PartnerCondition::className(), ['id' => 'cond_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAct()
    {
        return $this->hasOne(Acts::className(), ['id' => 'act_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['id' => 'partner_id']);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $pb = parent::behaviors();
        return ArrayHelper::merge($pb,[
            [
                'class' => LogModelBehavior::className(),       //логирование изменения
                'ignored' => ['created_at','updated_at']
            ],
        ]);
    }
}
