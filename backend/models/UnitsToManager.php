<?php

namespace app\models;

use common\components\helpers\CustomHelper;
use common\models\AbstractActiveRecord;
use Yii;
use backend\models\BUser;
use common\models\Payments;
use yii\caching\DbDependency;

/**
 * This is the model class for table "{{%units_to_manager}}".
 *
 * @property integer $id
 * @property integer $unit_id
 * @property integer $cost
 * @property integer $manager_id
 * @property integer $payment_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $pay_date
 * @property Payments $payment
 * @property BUser $manager
 * @property Units $unit
 */
class UnitsToManager extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%units_to_manager}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['unit_id', 'cost', 'manager_id'], 'required'],
            [[
                 'unit_id', 'cost', 'manager_id',
                 'payment_id', 'created_at', 'updated_at',
                 'pay_date'
             ], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/units', 'ID'),
            'unit_id' => Yii::t('app/units', 'Unit ID'),
            'cost' => Yii::t('app/units', 'Cost'),
            'manager_id' => Yii::t('app/units', 'Manager ID'),
            'payment_id' => Yii::t('app/units', 'Payment ID'),
            'pay_date' => Yii::t('app/units', 'Pay date'),
            'created_at' => Yii::t('app/units', 'Created At'),
            'updated_at' => Yii::t('app/units', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->hasOne(Payments::className(), ['id' => 'payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(BUser::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Units::className(), ['id' => 'unit_id']);
    }

    /**
     * Получение юнитов за текущий месяц
     * @param $manID
     * @return array
     */
    public static function getManagerUnitsByCurrMonthRange($manID)
    {
        $time = CustomHelper::getBeginMonthTime();
        $dep = new DbDependency([
            'sql' => 'SELECT MAX(updated_at) as s FROM '.self::tableName().' WHERE updated_at > '.$time.' AND manager_id = '.$manID
        ]);

        $tmp = self::getDb()->cache(function($db) use ($time,$manID){
            return UnitsToManager::find()
                ->select(['unit_id','cost','payment_id'])
                ->where(' updated_at > '.$time)
                ->andWhere(['manager_id' => $manID])
                ->all();
        },3600*24,$dep);

        $iTotalCost = 0;
        foreach($tmp as $t)
        {
            $iTotalCost+=(int)$t->cost;
        }

        $iTotalUnits = count($tmp);

        return [
          'iTotalCost' => $iTotalCost,
          'iTotalUnits' => $iTotalUnits,
          'arUnits' => $tmp
        ];
    }

    /**
     * Получение юнитов за период
     * @param $manID
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getManagerUnitsByDateRange($manID,$startDate,$endDate)
    {
        $startDate = is_numeric($startDate) ? $startDate : strtotime('00:00:01 '.$startDate);
        $endDate = is_numeric($endDate) ? $endDate : strtotime('23:59:59 '.$endDate);

        $dep = new DbDependency([
            'sql' => 'SELECT MAX(updated_at) as s FROM '.self::tableName().
                ' WHERE updated_at >= '.$startDate.' AND  updated_at <= '.$endDate.' AND manager_id = '.$manID
        ]);

        $tmp = self::getDb()->cache(function($db) use ($startDate,$endDate,$manID){
            return UnitsToManager::find()
                //->select(['unit_id','cost','payment_id'])
                ->with('payment','unit')
                ->where(' updated_at >= '.$startDate)
                ->andWhere(' updated_at <= '.$endDate)
                ->andWhere(['manager_id' => $manID])
                ->all();
        },3600*24,$dep);

        $iTotalCost = 0;
        foreach($tmp as $t)
        {
            $iTotalCost+=(int)$t->cost;
        }

        $iTotalUnits = count($tmp);

        return [
            'iTotalCost' => $iTotalCost,
            'iTotalUnits' => $iTotalUnits,
            'arUnits' => $tmp
        ];

    }
}
