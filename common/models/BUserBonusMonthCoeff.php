<?php

namespace common\models;

use common\components\bonus\BonusRecordCalculate;
use common\components\helpers\CustomDateHelper;
use Yii;
use common\components\helpers\CustomHelper;
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

    public static function getByUserAndDate($users, $start, $end){
        $start = date("Y-m", strtotime($start));
        $end = date("Y-m", strtotime($end));
        $coeffs = static::find()->where(['buser_id'=>$users])->andWhere(['>=', "CONCAT(year,'-',month)",$start])->andWhere(['<=', "CONCAT(year,'-',month)",$end])->all();
        $now = date("Y-m");
        //если затронута дата из текущего месяца посчитаем текущие коэффициенты
        if($end>=$now){
            $bonusCalculate = new BonusRecordCalculate();
            $nextMonthCoeffs = $bonusCalculate->getCoeffNextMonth($users, time());
            $month = CustomHelper::getEndMonthTime(time());
            foreach($nextMonthCoeffs as $key=>$val){
                $nextMonthCoeff = new static();
                $nextMonthCoeff->buser_id = $key;
                $nextMonthCoeff->year = date("Y", $month+10);
                $nextMonthCoeff->month = date("m", $month+10);
                $nextMonthCoeff->coeff = $val;
                $coeffs[] = $nextMonthCoeff;
            }
        }
        $allCoeff = [];
        foreach($coeffs as $coeff){
            $allCoeff[$coeff->buser_id][$coeff->year.'-'.$coeff->month] =  $coeff;
        }
        foreach($users as $user){
            $keyMonth = $start;
            do{
                $next = true;
                $date = strtotime($keyMonth.'-01');
                if(!isset($allCoeff[$user][$keyMonth])){
                    $nextMonthCoeff = new static();
                    $nextMonthCoeff->buser_id = $user;
                    $nextMonthCoeff->year = date("Y", $date);
                    $nextMonthCoeff->month = date("m", $date);
                    $nextMonthCoeff->coeff = 1;
                    $allCoeff[$user][$keyMonth] = $nextMonthCoeff;
                }
                $date = CustomHelper::getEndMonthTime($date);
                $keyMonth = date("Y-m", $date+10);
                if($keyMonth > $end)
                    $next = false;
            }while($next);
            ksort($allCoeff[$user]);
        }
        ksort($allCoeff);
        return $allCoeff;
    }

    public function getBuser()
    {
        return $this->hasOne(BUser::className(), ['id' => 'buser_id']);
    }
    public function getMonthName()
    {
        return CustomDateHelper::$month[$this->month-1].' '.$this->year;
    }
}
