<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 15.07.15
 */

namespace backend\modules\reports\forms;

use common\models\CUser;
use yii\base\Model;
use Yii;

class ContractorReportForm extends Model{

    public
        $dateFrom,
        $dateTo,
        $showDetail,
        $showManager,
        $period;

    /**
     * @return array
     */
    public function rules()
    {
        return  [
          [['dateTo', 'dateFrom'], 'required'],
          [['dateTo', 'dateFrom'],'validateDate']

        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateDate($attribute, $params)
    {
        if(strtotime($this->dateFrom) > strtotime($this->dateTo))
        {
            $this->addError($attribute,Yii::t('app/reposrts','From date must be less than date to'));
        }
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'dateFrom' => Yii::t('app/reports','Date from'),
            'dateTo' => Yii::t('app/reports','Date to'),
            'showDetail' => Yii::t('app/reports','Show detail'),
            'showManager' => Yii::t('app/reports','Show manager'),
            'period' => Yii::t('app/reports','Period'),
        ];
    }

    public  function getReportData()
    {
        //Находим всех активных контрагентов.
        $arCA = CUser::find()->active()->all();
        return $arCA;
    }



} 