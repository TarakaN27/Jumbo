<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\forms;

use backend\models\BUser;
use common\components\helpers\PhpOfficeHelper;
use common\models\CUser;
use common\models\CuserProspects;
use common\models\CuserQuantityHour;
use common\models\CUserRequisites;
use common\models\EnrollmentRequest;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\Expense;
use common\models\ExpenseCategories;
use common\models\LegalPerson;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\Payments;
use common\models\PaymentsSale;
use common\models\Services;
use common\models\UnitsEnroll;
use yii\base\Model;
use Yii;
use common\models\PaymentsCalculations;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\JsExpression;

class ExpenseReportForm extends Model{

    CONST   //типы группировки
        GROUP_BY_DATE = 1,
        GROUP_BY_PARENT_CATEGORY = 2,
        GROUP_BY_CATEGORY = 3,
        GROUP_BY_LEGAL_PERSON = 4,
        GROUP_BY_CONTRACTOR = 5;

    const MONTH = 31;

    public
        $groupType = self::GROUP_BY_DATE,
        $expenseCategory,
        $services,
        $contractor,
        $managers,
        $legalPerson,
        $dateFrom,
        $generateExcel,
        $dateTo
        ;

    protected
        $arPaymentsInByr = [],      //платежи в белорусских рублях
        $arSales = [];              //продажи
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['dateFrom','dateTo'],'required'],
            [['dateFrom','dateTo'],'date','format' => 'php:d.m.Y'],
            [['expenseCategory','contractor','legalPerson'],'safe'],
            [['expenseCategory','contractor','legalPerson'],'safe'],
            [['generateExcel','groupType'],'integer'],
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
            'expenseCategory' => Yii::t('app/reports','Expense category'),
            'contractor' => Yii::t('app/reports','Contractor'),
            'legalPerson' => Yii::t('app/reports','Legal person'),
            'dateFrom' => Yii::t('app/reports','Date from'),
            'dateTo' => Yii::t('app/reports','Date to'),
            'generateExcel' => Yii::t('app/reports','Generate excel'),
            'groupType' => Yii::t('app/reports','Group type'),
        ];
    }

    /**
     * @return array
     */
    public static function getGroupByMap()
    {
        $arGroup = [
            self::GROUP_BY_DATE => Yii::t('app/reports','Group by date'),
            self::GROUP_BY_PARENT_CATEGORY => Yii::t('app/reports','Group by parent category'),
            self::GROUP_BY_CATEGORY => Yii::t('app/reports','Group by category'),
            self::GROUP_BY_LEGAL_PERSON => Yii::t('app/reports','Group by legal'),
            self::GROUP_BY_CONTRACTOR => Yii::t('app/reports','Group by contractor')
        ];

        return $arGroup;
    }


    /**
     * @return string
     */
    public function getGroupByStr()
    {
        $tmp = self::getGroupByMap();
        return isset($tmp[$this->groupType]) ? $tmp[$this->groupType] : 'N/A';
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = Expense::find();
        $arSelect = [
            Expense::tableName() . '.id',
            Expense::tableName() . '.pay_date',
            Expense::tableName() . '.pay_summ',
            Expense::tableName() . '.currency_id',
            ExpenseCategories::tableName() . '.name as cat_name',
            ExpenseCategories::tableName() . '.ignore_at_report as ignore',
            ExpenseCategories::tableName() . '.parent_id',
            CUser::tableName() . '.requisites_id',
            CUserRequisites::tableName() . '.corp_name',
            CUserRequisites::tableName() . '.j_fname',
            CUserRequisites::tableName() . '.j_lname',
            CUserRequisites::tableName() . '.j_mname',
            CUserRequisites::tableName() . '.type_id',
            LegalPerson::tableName() . '.name as legal_name',
            ExchangeRates::tableName() . '.name as curr_name',
            ExchangeRates::tableName() . '.code',
            ExchangeRates::tableName() . '.nbrb_rate',
        ];
        $data->joinWith('cat');
        $data->joinWith('cuser.requisites');
        $data->joinWith('legal');
        $data->joinWith('currency');


        $data->select($arSelect);
        $data->andWhere(
            Expense::tableName() . '.pay_date >= "' . strtotime($this->dateFrom . ' 00:00:00 ') . '"' .
            ' AND ' . Expense::tableName() . '.pay_date <= "' . strtotime($this->dateTo . ' 23:59:59') . '"'
        );

        //пункт "Без контрагентов" (ид = -1) добавил в контроллере
        if (isset($this->contractor[0]))
            if ($this->contractor[0] == "-1") {
                $this->contractor[0] = null;
                $data->andWhere(['or',
                    [Expense::tableName() . '.cuser_id' => $this->contractor],
                    [Expense::tableName() . '.cuser_id' => null],
                ]);
                $this->contractor[0] = "-1";
            } else {
                $data->andWhere([
                    Expense::tableName() . '.cuser_id' => $this->contractor,
                ]);
            }

        if (!isset($this->expenseCategory[0])) {
            $data->andWhere([
                Expense::tableName() . '.cat_id' => array_keys(ExpenseCategories::getExpenseCatTreeGroupSelectable()),
            ]);
        } else {
            $data->andWhere([
                Expense::tableName() . '.cat_id' => $this->ifParentCategory($this->expenseCategory),
            ]);
        }

        $data->andFilterWhere([
            Expense::tableName() . '.legal_id' => $this->legalPerson,
        ]);

        $data->orderBy(Expense::tableName() . '.pay_date ASC');
        $dataForGraph = clone $data;
        $data = $data->createCommand()->queryAll();

        $arResult = [
            'data' => [],
            'excelLink' => '',
            'currency' => [],
            'condCurr' => [],
            'docxLink' => '',
            'iExpenseTotal' => 0,
            'iExpenseReportsTotal' => 0,
            'summControll' => 0,
            'totalGroupSum' => [],
            'totalGroupProfit' => [],
            'fullAmount' => [],
            'graphArray' => []
        ];
        $totalGroupSum = [];
        $totalGroupProfit = [];

        $dateMask = '%Y-%m-%d';
        if ((strtotime($this->dateTo) - strtotime($this->dateFrom)) / 86400 >= self::MONTH) {
            $dateMask = '%Y-%m';
        }

        $dataForGraph = $dataForGraph->addSelect(
            new Expression("FROM_UNIXTIME(" . Expense::tableName() . ".pay_date,'" . $dateMask . "') as pay_date2, sum(" . Expense::tableName() . ".pay_summ*" . ExchangeRates::tableName() . ".nbrb_rate) as day_sum"));
        //добавляю группировку по полю, в зависимости от типа группировки
        switch ($this->groupType) {
            case self::GROUP_BY_DATE:
                $dataForGraph = $dataForGraph->groupBy(['pay_date2']);
                break;
            case self::GROUP_BY_PARENT_CATEGORY:
                $dataForGraph = $dataForGraph->groupBy(['pay_date2', ExpenseCategories::tableName() . '.parent_id']);
                break;
            case self::GROUP_BY_CATEGORY:
                $dataForGraph = $dataForGraph->groupBy(['pay_date2', 'cat_name']);
                break;
            case self::GROUP_BY_LEGAL_PERSON:
                $dataForGraph = $dataForGraph->groupBy(['pay_date2', 'legal_name']);
                break;
            case self::GROUP_BY_CONTRACTOR:
                $dataForGraph = $dataForGraph->groupBy(['pay_date2', CUserRequisites::tableName() . '.corp_name']);
                break;
            default:
                $dataForGraph = $dataForGraph->groupBy(['pay_date2']);
                break;
        }

        $dataForGraph = $dataForGraph->createCommand()->queryAll();

        $obECH = new ExchangeCurrencyHistory();
        $arCurrIds = array_unique(ArrayHelper::getColumn($data, 'currency_id'));

        if (!empty($arCurrIds))
            $arCurrInBur = $obECH->getCurrencyInByrForPeriod(strtotime($this->dateFrom), strtotime($this->dateTo), $arCurrIds);
        else
            $arCurrInBur = [];
        if (!empty($dataForGraph)){
            foreach ($dataForGraph as $dt) {
                $date = $dt['pay_date2'];

                switch ($this->groupType) {
                    case self::GROUP_BY_DATE:
                        $arResult['graphArray']['data'][$date][$date] = round($dt['day_sum'], 2);

                        break;
                    case self::GROUP_BY_PARENT_CATEGORY:
                        $groupValue = ExpenseCategories::getParentCat()[$dt['parent_id']];
                        $arResult['graphArray']['data'][$date][$groupValue] = round($dt['day_sum'], 2);

                        break;
                    case self::GROUP_BY_CATEGORY:
                        $groupValue = $dt['cat_name'];
                        $arResult['graphArray']['data'][$date][$groupValue] = round($dt['day_sum'], 2);

                        break;
                    case self::GROUP_BY_LEGAL_PERSON:
                        $groupValue = $dt['legal_name'];
                        $arResult['graphArray']['data'][$date][$groupValue] = round($dt['day_sum'], 2);

                        break;
                    case self::GROUP_BY_CONTRACTOR:
                        if ($dt['requisites_id']) {
                            $corpName = CUserRequisites::getCorpNameWithSiteByDataArray($dt);
                        } else {
                            $corpName = Yii::t('app/reports', 'Without contractor');
                        }

                        $arResult['graphArray']['data'][$date][$corpName] = round($dt['day_sum'], 2);

                        break;
                    default:
                        break;
                }
            }

            $graphsData = [];
            foreach ($arResult['graphArray']['data'] as $key => $item) {
                foreach ($item as $element => $sum) {
                    $day = explode('-', $key);
                    $dayForJs = implode(',', $day);

                    $graphsData[$element][] = "[Date.UTC($dayForJs)," . $sum . "]";
                }
            }

            $seriesData = [];
            $arResult['graphArray']['legend'] = true;

            if ($this->groupType == ExpenseReportForm::GROUP_BY_DATE) {
                $seriesData[0]['name'] = Yii::t('app/reports','Expense sum');
                $arResult['graphArray']['legend'] = false;
                foreach ($graphsData as $element => $item) {
                    $seriesData[0]['data'][] = $item[0];
                }

                $seriesData[0]['data'] = new JsExpression('[' . implode(",", $seriesData[0]['data']) . ']');
            } else {
                $i = 0;
                foreach ($graphsData as $element => $item) {
                    $seriesData[$i]['name'] = $element;
                    $seriesData[$i]['data'] = new JsExpression('[' . implode(",", $item) . ']');
                    $i++;
                }
            }

            $arResult['graphArray']['data'] = $seriesData;
        }



        /** @var Payments $dt */
        foreach($data as $dt)
        {
            $date = date('Y-m-d',$dt['pay_date']);

            $dt['full_corp_name'] = false;
            if($dt['requisites_id']){
                $dt['full_corp_name'] = CUserRequisites::getCorpNameByDataArray($dt);
            }

            if(!isset($arCurrInBur[$dt['currency_id']],$arCurrInBur[$dt['currency_id']][$date]))
                throw new NotFoundHttpException('Exchange rate for currency '.$dt['currency_id'].' at  '.$date.' not found.');

            $iCurr = $arCurrInBur[$dt['currency_id']][$date];

            $arResult['data'][$date][] = $dt;
            $totalGroupSum = $this->totalHelper($totalGroupSum,$date,$dt['pay_summ']*$iCurr);

            $this->arPaymentsInByr[$dt['id']] = (float)$dt['pay_summ']*$iCurr;
            $arResult['iExpenseTotal']+= ($dt['pay_summ']*$iCurr);
            $arResult['currency'][$dt['id']] = $iCurr;
            $arResult['fullAmount'][$dt['id']] = $dt['pay_summ']*$iCurr;


            if($dt['ignore'] != ExpenseCategories::IGNORED)
            {
                $arResult['iExpenseReportsTotal']+= $dt['pay_summ']*$iCurr;
            }

        }

        unset($data);
        
        $arResult['totalGroupSum'] = $totalGroupSum;
        $arResult['totalGroupProfit'] = $totalGroupProfit;

        if($this->generateExcel)
            $arResult['excelLink'] = $this->generateExcelDocument($arResult);

        return $arResult;
    }

    protected function ifParentCategory($category){

        if(isset($category) && $category !=  ""){
            $result = [];
            $categoryArr = ExpenseCategories::find()->select('id')->where(['parent_id' => 0])->asArray()->all();
            foreach ($categoryArr as $pCat){
                if(in_array($pCat['id'] , $category)){
                    $result[] = $pCat['id'];
                }
            }
            $categoryArr = ExpenseCategories::find()->select('id')->where(['parent_id' => $result])->asArray()->all();

            foreach ($categoryArr as $item){
                if(!in_array($item['id'], $category)){
                    $category[] = $item['id'];
                }
            }
        }

        return $category;
    }

    /**
     * @param $arArray
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function totalHelper($arArray,$key,$value)
    {
        if(isset($arArray[$key]))
            $arArray[$key]+=$value;
        else
            $arArray[$key]=$value;

        return $arArray;
    }

    /**
     * @param $data
     * @return null|string
     */
    protected function generateExcelDocument($data)
    {
        if(empty($data))
            return NULL;

        $sFileName = 'expense-report-'.uniqid(time()).'.xlsx';

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->user->id)
            ->setTitle(Yii::t('app/reports','Expense report'))
            ->setSubject(Yii::t('app/reports','Expense report'));

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A1', Yii::t('app/reports','Expense report'))
            ->setCellValue('A2', Yii::t('app/reports','Date from'))
            ->setCellValue('B2', Yii::$app->formatter->asDate($this->dateFrom))
            ->setCellValue('A3', Yii::t('app/reports','Date to'))
            ->setCellValue('B3', Yii::$app->formatter->asDate($this->dateTo))
            ->setCellValue('A4', Yii::t('app/reports','Expense total'))
            ->setCellValue('B4', $data['iExpenseTotal'])
            ->setCellValue('A5', Yii::t('app/reports','Reported total'))
            ->setCellValue('B5', $data['iExpenseReportsTotal']);


        $objPHPExcel->getActiveSheet()
            ->setCellValue('A9', 'Детализация платежей:');

        $objPHPExcel->getActiveSheet()->setCellValue('A9',Yii::t('app/reports','Payments date'));
        $objPHPExcel->getActiveSheet()->setCellValue('B9',Yii::t('app/reports','Expense category'));
        $objPHPExcel->getActiveSheet()->setCellValue('C9',Yii::t('app/reports','Contractor'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9',Yii::t('app/reports','Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('E9',Yii::t('app/reports','Expense sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9',Yii::t('app/reports','Expense currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9',Yii::t('app/reports','Expense BYR'));

        $i=10;

            foreach($data['data'] as $key=>$dt)
            {
                foreach($dt as $d)
                {
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,Yii::$app->formatter->asDate($d['pay_date']));
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,($d['cat_name'] ? $d['cat_name'] : 'N/A'));
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,($d['full_corp_name'] ? $d['full_corp_name'] : 'N/A'));
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,($d['legal_name'] ? $d['legal_name'] : 'N/A'));
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$d['pay_summ']);

                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,($d['code']?$d['code'] : 'N/A'));

                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,($data['fullAmount'][$d['id']] ? $data['fullAmount'][$d['id']] : 'N/A'));

                    $i++;
                }
            }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(Yii::getAlias('@backend/web/reports/').$sFileName);

        return $sFileName;
    }


    /**
     *
     */
    protected function getSales()
    {
        if(empty($this->arPaymentsInByr))
            return [];

        return $this->arSales = PaymentsSale::find()->select(['payment_id'])->where(['payment_id' => array_keys($this->arPaymentsInByr)])->column();
    }

    /**
     * @return array
     */
    protected function getSaleInfoAmount()
    {
        if(empty($this->arPaymentsInByr))
            return ['saleAmount' => 0,'paymentWithoutSale' => 0];

        if(empty($this->arSales))
        {
            return ['saleAmount' => 0,'paymentWithoutSale' => array_sum($this->arPaymentsInByr)];
        }

        $amountSale = 0;
        $amountPayment = 0;

        foreach ($this->arPaymentsInByr as $iPayId => $payAmount)
        {
            if(in_array($iPayId,$this->arSales))
            {
                $amountSale+=(float)$payAmount;
            }else{
                $amountPayment+=(float)$payAmount;
            }
        }

        return ['saleAmount' => $amountSale,'paymentWithoutSale' => $amountPayment];
    }

} 