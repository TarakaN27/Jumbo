<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */

namespace backend\modules\reports\forms;


use app\models\UnitsToManager;
use yii\base\Model;
use Yii;

class DetailUnitsViewForm extends Model{

    public
        $dateFrom,
        $dateTo,
        $manID;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['dateFrom','dateTo'],'required'],
            [['dateFrom','dateTo'],'date','format' => 'php:d.m.Y'],
            [['manID'],'integer'],
            [['dateFrom','dateTo'],'validatePeriodDate'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatePeriodDate($attribute, $params)
    {
        if(strtotime($this->dateTo)<strtotime($this->dateFrom))
            $this->addError($attribute, Yii::t('app/reports','Date to must be more than date from'));
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'manID' => Yii::t('app/reports','Managers'),
            'dateFrom' => Yii::t('app/reports','Date from'),
            'dateTo' => Yii::t('app/reports','Date to'),
        ];
    }

    /**
     * @return array
     */
    public function makeRequest()
    {
        return UnitsToManager::getManagerUnitsByDateRange($this->manID,$this->dateFrom,$this->dateTo);
    }
} 