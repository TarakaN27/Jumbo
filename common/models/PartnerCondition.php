<?php

namespace common\models;

use common\components\loggingUserBehavior\LogModelBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%partner_condition}}".
 *
 * @property integer $id
 * @property integer $min_amount
 * @property integer $max_amount
 * @property double $percent
 * @property string $start_date
 * @property integer $created_at
 * @property integer $updated_at
 */
class PartnerCondition extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_condition}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['min_amount', 'max_amount', 'percent', 'start_date'], 'required'],
            [['min_amount', 'max_amount', 'created_at', 'updated_at'], 'integer'],
            [['percent'], 'number'],
            [['start_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/book', 'ID'),
            'min_amount' => Yii::t('app/book', 'Min Amount'),
            'max_amount' => Yii::t('app/book', 'Max Amount'),
            'percent' => Yii::t('app/book', 'Percent'),
            'start_date' => Yii::t('app/book', 'Start Date'),
            'created_at' => Yii::t('app/book', 'Created At'),
            'updated_at' => Yii::t('app/book', 'Updated At'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $pb = parent::behaviors();
        return ArrayHelper::merge($pb,[
            [
                'class' => LogModelBehavior::className(),       //логирование изменения условий
                'ignored' => ['created_at','updated_at']
            ],
        ]);
    }
}
