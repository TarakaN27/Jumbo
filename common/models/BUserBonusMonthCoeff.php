<?php

namespace common\models;

use Yii;
use backend\models\BUser;
/**
 * This is the model class for table "{{%b_user_bonus}}".
 *
 * @property integer $id
 * @property string $amount
 * @property integer $buser_id
 * @property integer $scheme_id
 * @property integer $payment_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $service_id
 * @property integer $cuser_id
 * @property integer $currency_id
 * @property integer $record_id
 *
 * @property Payments $payment
 * @property BUser $buser
 * @property BonusScheme $scheme
 */
class BUserBonusMonthCoeff extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%b_user_bonus_month_coeff}}';
    }
}
