<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */

namespace backend\modules\reports\forms;


use app\models\UnitsToManager;
use backend\models\BUser;
use yii\base\Model;
use Yii;
use yii\db\Query;

class UnitsReportForm extends Model{

    public
        $managers,
        $dateFrom,
        $generateExcel,
        $generateDocx,
        $dateTo;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['dateFrom','dateTo'],'required'],
            [['dateFrom','dateTo'],'date','format' => 'yyyy-M-d'],
            [['managers'],'safe'],
            [['generateExcel','generateDocx'],'integer'],
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
            'managers' => Yii::t('app/reports','Managers'),
            'dateFrom' => Yii::t('app/reports','Date from'),
            'dateTo' => Yii::t('app/reports','Date to'),
            'generateExcel' => Yii::t('app/reports','Generate excel'),
            'generateDocx' => Yii::t('app/reports','Generate docx'),
        ];
    }

    /**
     * @return array
     */
    public function getData()
    {
        $arData = [];
        $data = (new Query())
            ->select('id,cost,manager_id')
            ->from(UnitsToManager::tableName())
            ->where(' created_at >= '.strtotime('00:00:01 '.$this->dateFrom).
                ' AND created_at <= '.strtotime('23:59:59 '.$this->dateTo))
            ->andFilterWhere(['manager_id' => $this->managers])
            ->all();

        if(empty($data))
            return [];
        $arMID = [];
        $arTMP = [];
        foreach($data as $d)
        {
            $arMID[] = $d['manager_id'];
            if(isset($arTMP[$d['manager_id']]))
            {
                $arTMP[$d['manager_id']]['cost']+=$d['cost'];
                $arTMP[$d['manager_id']]['units']++;
            }else{
                $arTMP[$d['manager_id']]['cost'] = $d['cost'];
                $arTMP[$d['manager_id']]['units'] = 1;
            }
        }

        $arManagers = BUser::find()->select(['id','username','fname','lname','mname'])->where(['id' => $arMID])->all();
        /** @var BUser $obMan */
        foreach($arManagers as $obMan)
        {
            if(isset($arTMP[$obMan->id]))
            {
                $arData[] = [
                    'id' => $obMan->id,
                    'username' => $obMan->username,
                    'fio' => $obMan->getFio(),
                    'cost' => $arTMP[$obMan->id]['cost'],
                    'units' => $arTMP[$obMan->id]['units']
                ];
            }
        }

        return [
            'data' => $arData,
            'excelLink' => $this->generateExcel ? $this->generateExcelDocument($arData) : NULL,
            'docxLink' => $this->generateDocx ? $this->generateDocxDocument($arData) : NULL
        ];
    }

    /**
     * @param $data
     * @return null|string
     */
    protected function generateExcelDocument($data)
    {
       return NULL;

    }

    /**
     * @param $data
     * @return null|string
     */
    protected function generateDocxDocument($data)
    {
        return NULL;
    }
} 