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
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class ExpenseReportForm extends Model{

    CONST   //типы группировки
        GROUP_BY_DATE = 1,
        GROUP_BY_PARENT_CATEGORY = 2,
        GROUP_BY_CATEGORY = 3,
        GROUP_BY_LEGAL_PERSON = 4,
        GROUP_BY_CONTRACTOR = 5;

    public
        $groupType = self::GROUP_BY_DATE,
        $paymentCategory,
        $services,
        $contractor,
        $managers,
        $legalPerson,
        $dateFrom,
        $generateExtendExcel,
        $generateExcel,
        $generateDocx,
        $dateTo,
        $showWithoutSale
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
            [['paymentCategory','contractor','legalPerson'],'safe'],
            [['paymentCategory','contractor','legalPerson'],'safe'],
            [['generateExcel','generateDocx','groupType','generateExtendExcel','showWithoutSale'],'integer'],
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
            'paymentCategory' => Yii::t('app/reports','Payment category'),
            'contractor' => Yii::t('app/reports','Contractor'),
            'managers' => Yii::t('app/reports','Managers'),
            'legalPerson' => Yii::t('app/reports','Legal person'),
            'dateFrom' => Yii::t('app/reports','Date from'),
            'dateTo' => Yii::t('app/reports','Date to'),
            'generateExcel' => Yii::t('app/reports','Generate excel'),
            'generateDocx' => Yii::t('app/reports','Generate docx'),
            'groupType' => Yii::t('app/reports','Group type'),
            'generateExtendExcel' => Yii::t('app/reports','Generate extend excel'),
            'showWithoutSale' => Yii::t('app/reports','Show without sale')
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
        $data = Expense::find();//->with('calculate','cuser','legal','service','calculate.payCond');
        $arSelect = [
            Expense::tableName().'.id',
            Expense::tableName().'.pay_date',
            Expense::tableName().'.pay_summ',
            Expense::tableName().'.currency_id',
            ExpenseCategories::tableName().'.name as cat_name',
            ExpenseCategories::tableName().'.ignore_at_report as ignore',
            ExpenseCategories::tableName().'.parent_id',
            CUser::tableName().'.requisites_id',
            CUserRequisites::tableName().'.corp_name',
            CUserRequisites::tableName().'.j_fname',
            CUserRequisites::tableName().'.j_lname',
            CUserRequisites::tableName().'.j_mname',
            CUserRequisites::tableName().'.type_id',
            LegalPerson::tableName().'.name as legal_name',
            ExchangeRates::tableName().'.name as curr_name',
            ExchangeRates::tableName().'.code',
            ExchangeRates::tableName().'.nbrb_rate',
        ];
        $data->joinWith('cat');
        $data->joinWith('cuser.requisites');
        $data->joinWith('legal');
        $data->joinWith('currency');
        if($this->generateExtendExcel)
        {/*
            $data->joinWith('cuser.quantityHour');
            $data->joinWith('cuser.prospects');
            $data->joinWith('cuser.manager manager');
            array_push($arSelect,CuserProspects::tableName().'.name as prospects_name');
            array_push($arSelect,CuserQuantityHour::tableName().'.cuser_id as quant_user');
            array_push($arSelect,CuserQuantityHour::tableName().'.hours');
            array_push($arSelect,CuserQuantityHour::tableName().'.spent_time');
            $arSelect['managerFname'] ='manager.fname';
            $arSelect['managerLname'] ='manager.lname';
            $arSelect['managerMname'] ='manager.mname';
            $data->joinWith('enrollRequest');
            $data->joinWith('enrollRequest.unitEnroll');
            $data->andWhere(EnrollmentRequest::tableName().'.parent_id is null');
            array_push($arSelect,EnrollmentRequest::tableName().'.amount as enroll_amount');
            array_push($arSelect,UnitsEnroll::tableName().'.name as enroll_unit_name');
*/
        }

        $data->select($arSelect);
        $data->andWhere(
            Expense::tableName().'.pay_date >= "'.strtotime($this->dateFrom.' 00:00:00 ').'"'.
            ' AND '.Expense::tableName().'.pay_date <= "'.strtotime($this->dateTo.' 23:59:59').'"'
        );


        //var_dump($this->contractor);die;

        if($this->contractor[0] == "-1"){
            $data->andWhere([
                Expense::tableName().'.cuser_id' => null,
            ]);
        }elseif(isset($this->contractor[0])){
            $data->andWhere([
                Expense::tableName().'.cuser_id' => $this->contractor,
            ]);
        }
        $data->andFilterWhere([
            Expense::tableName().'.cat_id' => $this->paymentCategory,
            Expense::tableName().'.legal_id' => $this->legalPerson,
        ]);

        //если массив то значит это смотрит админ, ему показываем только те платежи у которых владелец выбранный менеджер,
        //иначе покажем менеджеру все платежи которые он должен видеть
        /*if($this->managers && !is_array($this->managers)){
            $cuserIdSales = PaymentsSale::find()->select(['cuser_id'])->where(['buser_id'=>$this->managers])->asArray()->all();
            if($cuserIdSales) {
                $cuserIdSales = ArrayHelper::getColumn($cuserIdSales, 'cuser_id');
                $data->andWhere(['or', [PaymentRequest::tableName() . '.manager_id' => $this->managers], [CUser::tableName() . '.manager_id' => $this->managers], [Payments::tableName() . '.cuser_id' => $cuserIdSales]]);
            }else
                $data->andWhere(['or', [PaymentRequest::tableName() . '.manager_id' => $this->managers], [CUser::tableName() . '.manager_id' => $this->managers], [Payments::tableName() . '.cuser_id' => $cuserIdSales]]);
        }else{
                $data->andFilterWhere([
                    PaymentRequest::tableName().'.manager_id' => $this->managers
                ]);
        }
*/

        $data->orderBy(Expense::tableName().'.pay_date ASC');
        $data = $data->createCommand()->queryAll();

        $arResult = [
            'data' => [],
            'excelLink' => '',
            'currency' => [],
            'condCurr' => [],
            'docxLink' => '',
            'iExpenseTotal' => 0,
            'iExpenseReportsTotal' => 0,
            'iTaxTotal' => 0,
            'iProdTotal' => 0,
            'summControll' => 0,
            'totalGroupSum' => [],
            'totalGroupProfit' => [],
            'totalGroupTax' => [],
            'totalGroupProd' => [],
            'fullAmount' => [],
            'quantityHours' => [],
            'graphArray' => []
        ];
        $arCurr = [];
        $arCondition = [];
        $totalGroupSum = [];
        $totalGraphGroupSum = [];
        $totalGroupProfit = [];

        // курсы обмена валют за период

        //var_dump($data[0]['currency_id']);die;

        $obECH = new ExchangeCurrencyHistory();
        $arCurrIds = array_unique(ArrayHelper::getColumn($data,'currency_id'));

        if(!empty($arCurrIds))
            $arCurrInBur = $obECH->getCurrencyInByrForPeriod(strtotime($this->dateFrom),strtotime($this->dateTo),$arCurrIds);
        else
            $arCurrInBur = [];
        /** @var Payments $dt */
        foreach($data as $dt)
        {
            $date = date('Y-m-d',$dt['pay_date']);
            $iCurr = 0;
            $dt['full_corp_name'] = false;
            if($dt['requisites_id']){
                $dt['full_corp_name'] = CUserRequisites::getCorpNameByDataArray($dt);
            }

            if(!isset($arCurrInBur[$dt['currency_id']],$arCurrInBur[$dt['currency_id']][$date]))
                throw new NotFoundHttpException('Exchange rate for currency '.$dt['currency_id'].' at  '.$date.' not found.');

            $iCurr = $arCurrInBur[$dt['currency_id']][$date];

            $iCondCurr = 0;
            if($dt['pay_cond_id'] && ($dt['pay_cond_name']))
            {
                if( isset($arCurr[$date]) && isset($arCurr[$date][$dt['cond_currency']]))
                {
                    $iCondCurr = $arCurr[$date][$dt['cond_currency']];
                }else{
                    $iCondCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$dt['cond_currency']);
                    $arCurr[$date][$dt['cond_currency']] = $iCondCurr;
                }
                $arCondition [] = $dt['pay_cond_id'];
            }
            //$arResult['data'][$date][] = $dt;
            //$totalGroupSum = $this->totalHelper($totalGroupSum,$date,$dt['pay_summ']*$iCurr);
            //формируем массив с данными
            switch ($this->groupType){
                case self::GROUP_BY_DATE:
                    $arResult['data'][$date][] = $dt;
                    $totalGroupSum = $this->totalHelper($totalGroupSum,$date,$dt['pay_summ']*$iCurr);

                    $totalGraphGroupSum = $this->totalHelper($totalGraphGroupSum,$date,$dt['pay_summ']*$iCurr);

                    $arResult['graphArray']['type'] = Yii::t('app/reports','Group by date');
                    $arResult['graphArray']['data'][$date][$date][] = round($dt['pay_summ']*$iCurr,2);

                    break;
                case self::GROUP_BY_PARENT_CATEGORY:
                    $parents = ExpenseCategories::getParentCat();

                    $groupValue = $parents[$dt['parent_id']];

                    $arResult['data'][$groupValue][] = $dt;
                    $totalGroupSum = $this->totalHelper($totalGroupSum,$groupValue,$dt['pay_summ']*$iCurr);

                    $totalGraphGroupSum = $this->totalHelper($totalGraphGroupSum,$groupValue,$dt['pay_summ']*$iCurr);

                    $arResult['graphArray']['type'] = Yii::t('app/reports','Group by parent category');
                    $arResult['graphArray']['data'][$date][$groupValue][] = round($dt['pay_summ']*$iCurr,2);

                    break;
                case self::GROUP_BY_CATEGORY:
                    $groupValue = $dt['cat_name'];

                    $arResult['data'][$dt['cat_name']][] = $dt;
                    $totalGroupSum = $this->totalHelper($totalGroupSum,$groupValue,$dt['pay_summ']*$iCurr);

                    $totalGraphGroupSum = $this->totalHelper($totalGraphGroupSum,$groupValue,$dt['pay_summ']*$iCurr);

                    $arResult['graphArray']['type'] = Yii::t('app/reports','Group by category');
                    $arResult['graphArray']['data'][$date][$groupValue][] = round($dt['pay_summ']*$iCurr,2);
                    break;
                case self::GROUP_BY_LEGAL_PERSON:
                    $groupValue = $dt['legal_name'];

                    $arResult['data'][$groupValue][] = $dt;
                    $totalGroupSum = $this->totalHelper($totalGroupSum,$groupValue,$dt['pay_summ']*$iCurr);

                    $totalGraphGroupSum = $this->totalHelper($totalGraphGroupSum,$groupValue,$dt['pay_summ']*$iCurr);

                    if(false)
                    if(isset($arResult['graphArray']['data'][$date][$groupValue])){
                        $arResult['graphArray']['data'][$date][$groupValue]+=($dt['pay_summ']*$iCurr);
                    }else{
                        $arResult['graphArray']['data'][$date][$groupValue]=($dt['pay_summ']*$iCurr);
                    }

/*
                    echo $date."<br>";
                    echo $groupValue."<br>";
                    echo $dt['pay_summ']*$iCurr."<br>";
                    echo "Only pay:<br>";
                    echo $dt['pay_summ']."<br>";
                    echo $iCurr."<br>";
                    echo $totalGraphGroupSum[$groupValue]."<br>";
                    echo "Graph:<br>";
                    echo $arResult['graphArray']['data'][$date][$groupValue]."<br>";
                    echo "<br>------------------<br>";
*/

                    $arResult['graphArray']['type'] = Yii::t('app/reports','Group by legal');
                    $arResult['graphArray']['data'][$date][$groupValue][] = $dt['pay_summ']*$iCurr;
                    break;
                case self::GROUP_BY_CONTRACTOR:
                    if($dt['requisites_id']){
                        $corpName = CUserRequisites::getCorpNameWithSiteByDataArray($dt);
                    }
                    if($corpName == ""){
                        $corpName = Yii::t('app/reports','Without contractor');
                    }
                    $arResult['data'][$corpName][] = $dt;
                    $totalGroupSum = $this->totalHelper($totalGroupSum,$corpName,$dt['pay_summ']*$iCurr);

                    $totalGraphGroupSum = $this->totalHelper($totalGraphGroupSum,$corpName,$dt['pay_summ']*$iCurr);

                    $arResult['graphArray']['type'] = Yii::t('app/reports','Group by contractor');
                    $arResult['graphArray']['data'][$date][$corpName][] = round($dt['pay_summ']*$iCurr, 2);
                    break;
                default:
                    break;
            }

            $this->arPaymentsInByr[$dt['id']] = (float)$dt['pay_summ']*$iCurr;              //соберем платежи в
            $arResult['iExpenseTotal']+= ($dt['pay_summ']*$iCurr);
            $arResult['currency'][$dt['id']] = $iCurr;
            $arResult['fullAmount'][$dt['id']] = $dt['pay_summ']*$iCurr;

            /**  @var CuserQuantityHour $obQuant */
            if($this->generateExtendExcel && $dt['requisites_id'] && $dt['quant_user'])
            {
                $arResult['quantityHours'][$dt['id']] = [
                    'paid' => $dt['hours'],
                    'spent' => $dt['spent_time'],
                    'balance' => $dt['hours'] - $dt['spent_time']
                ];
            }

            if($dt['ignore'] != ExpenseCategories::IGNORED)
            {
                $arResult['iExpenseReportsTotal']+= $dt['pay_summ']*$iCurr;
            }

            //var_dump($arResult['graphArray']['data']);
            if(false)
            foreach ($arResult['graphArray']['data'] as $data){
                foreach ($data as $key=>$value)
                    if($key == "Солюшнс Бел"){
                        var_dump($key);
                        var_dump($dt['pay_summ']*$iCurr);
                        echo "<br>---------------------<br>";
                    }
            }
        }
        //var_dump($totalGraphGroupSum);die;
        //var_dump($arResult['graphArray']['data']);die;

        unset($data);
        
        $arResult['totalGroupSum'] = $totalGroupSum;
        $arResult['totalGroupProfit'] = $totalGroupProfit;

        if($this->generateExcel)
            $arResult['excelLink'] = $this->generateExcelDocument($arResult);

/*        if($this->generateDocx)
            $arResult['docxLink'] = $this->generateDocxDocument($arResult);
*/
        if($this->generateExtendExcel)
            $arResult['excelExtendLink'] = $this->generateExtendExcelDocument($arResult,$arCondition);


//var_dump($arResult['graphArray']['data']);die;
        //die;

        if(count($arResult['graphArray']['data']) >= 2){
            $newData = [];
            foreach ($arResult['graphArray']['data'] as $date => $data){
                $dateArr = explode('-',$date);
                $newDate = $dateArr[0].'-'.$dateArr[1];
                //var_dump($data);
                //echo "<br>---------------------<br>";
                //$newData[$newDate][] = $data;
                /*
                var_dump($date);
                var_dump($data);
                echo "<br>---------------------<br>";*/
                if(true)
                foreach ($data as $key=>$value){
                    foreach ($value as $sum){
                        if(false)
                            if($key == "Солюшнс Бел"){
                                var_dump($value);
                                echo "old:<br>";
                                var_dump($newData[$newDate][$key]);
                                echo "<br>---------------------<br>";
                            }
                        if(isset($newData[$newDate][$key])){
                            $newData[$newDate][$key] += $sum;
                        }else{
                            $newData[$newDate][$key] = $sum;
                        }
                    }
                }
            }
            //var_dump($newData);            die;
            $arResult['graphArray']['data'] = $newData;
        }
//var_dump($arResult['graphArray']['data']);die;
        $newData = [];
        foreach($arResult['graphArray']['data'] as $date=>$data){
            foreach ($data as $key=>$value){
                foreach($arResult['graphArray']['data'] as $data2){
                    if((array_key_exists($key,$data2)) && ($data2[$key] == $value)){
                        $newData[$date][$key][] = $value;
                    }else{
                        $newData[$date][$key][] = null;
                    }
                }
            }
        }
        $arResult['graphArray']['data'] = $newData;

        $seriesData = [];
        $elementKeys = [];
//var_dump($model['graphArray']['data']);die;
        foreach ($arResult['graphArray']['data'] as $data){
            foreach ($data as $key=> $value){
                if(isset($elementKeys[$key])){

                    foreach ($arResult['graphArray']['data'] as $data){
                        foreach ($data as $key2 =>$value2){
                            for($i = 0; $i < count($value2); $i++){
                                if(($key == $key2) && (!$elementKeys[$key][$i])){
                                    $elementKeys[$key][$i] = $value[$i];
                                }
                            }
                        }
                    }

                }else{
                    $elementKeys[$key] = $value;
                }
            }

        }
        foreach ($elementKeys as $key=>$value){
            $element['type'] = 'column';
            $element['name'] = $key;
            $element['data'] = array_values($elementKeys[$key]);
            $seriesData[] = $element;
        }

        $arResult['graphArray']['data'] = $seriesData;
        //var_dump($newData);die;
//var_dump($arResult['graphArray']['data']);die;
        return $arResult;
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
            ->setCellValue('A1', 'Отчет по затратам')
            ->setCellValue('A2', 'Начальная дата:')
            ->setCellValue('B2', Yii::$app->formatter->asDate($this->dateFrom))
            ->setCellValue('A3', 'Конечная дата:')
            ->setCellValue('B3', Yii::$app->formatter->asDate($this->dateTo))
            ->setCellValue('A4', 'Общая сумма платежей:')
            ->setCellValue('B4', $data['iExpenseTotal'])
            ->setCellValue('A5', 'Общая прибыль:')
            ->setCellValue('B5', $data['iExpenseReportsTotal']);


        $objPHPExcel->getActiveSheet()
            ->setCellValue('A9', 'Детализация платежей:');

        $objPHPExcel->getActiveSheet()->setCellValue('A9',Yii::t('app/reports','Payments date'));
        $objPHPExcel->getActiveSheet()->setCellValue('B9',Yii::t('app/reports','Expense category'));
        $objPHPExcel->getActiveSheet()->setCellValue('C9',Yii::t('app/reports','Contractor'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9',Yii::t('app/reports','Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('E9',Yii::t('app/reports','Payment sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9',Yii::t('app/reports','Payment currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9',Yii::t('app/reports','Profit BYR'));

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
     * @param $data
     * @param array $arCondIDs
     * @return string
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function generateExtendExcelDocument($data,$arCondIDs = [])
    {
        if(empty($data))
            return NULL;

        $arCondIDs = array_unique($arCondIDs);
        $arCondTmp = (new Query())
            ->select(['cond.id','ex.code','ex.name'])
            ->from(PaymentCondition::tableName().' cond')
            ->leftJoin(ExchangeRates::tableName().' as ex','ex.id = cond.cond_currency')
            ->where(['cond.id' => $arCondIDs])
            ->all();

        $arCond = [];
        foreach($arCondTmp as $cond)
            $arCond[$cond['id']] = $cond;

        $sFileName = 'payments-extend-report-'.uniqid(time()).'.xlsx';

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->user->id)
            ->setTitle(Yii::t('app/reports','Payments report'))
            ->setSubject(Yii::t('app/reports','Payments report'));

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A1', 'Отчет по платежам')
            ->setCellValue('A2', 'Начальная дата:')
            ->setCellValue('B2', Yii::$app->formatter->asDate($this->dateFrom))
            ->setCellValue('A3', 'Конечная дата:')
            ->setCellValue('B3', Yii::$app->formatter->asDate($this->dateTo))
            ->setCellValue('A4', 'Общая сумма платежей:')
            ->setCellValue('B4', $data['iExpenseTotal'])
            ->setCellValue('A5', 'Общая прибыль:')
            ->setCellValue('B5', $data['iExpenseReportsTotal'])
            ->setCellValue('A6', 'Общий налог:')
            ->setCellValue('B6', $data['iTaxTotal'])
            ->setCellValue('A7', 'Общие производственные затраты:')
            ->setCellValue('B7', $data['iProdTotal']);


        $objPHPExcel->getActiveSheet()
            ->setCellValue('A9', 'Детализация платежей:');

        $objPHPExcel->getActiveSheet()->setCellValue('A9',Yii::t('app/reports','Payments ID'));
        $objPHPExcel->getActiveSheet()->setCellValue('B9',Yii::t('app/reports','Payments date'));
        $objPHPExcel->getActiveSheet()->setCellValue('C9',Yii::t('app/reports','Contractor'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9',Yii::t('app/reports','Prospects'));

        $objPHPExcel->getActiveSheet()->setCellValue('E9',Yii::t('app/reports','Paid hour'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9',Yii::t('app/reports','Spend hour'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9',Yii::t('app/reports','Balance hour'));

        $objPHPExcel->getActiveSheet()->setCellValue('H9',Yii::t('app/reports','Payment owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('I9',Yii::t('app/reports','Sale owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('J9',Yii::t('app/reports','Manager'));
        $objPHPExcel->getActiveSheet()->setCellValue('K9',Yii::t('app/reports','Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('L9',Yii::t('app/reports','Банк. реквизиты'));
        $objPHPExcel->getActiveSheet()->setCellValue('M9',Yii::t('app/reports','Service'));
        $objPHPExcel->getActiveSheet()->setCellValue('N9',Yii::t('app/reports','Payment sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('O9',Yii::t('app/reports','Payment currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('P9',Yii::t('app/reports','Exchange currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('Q9',Yii::t('app/reports','Full amount BYR'));

        $objPHPExcel->getActiveSheet()->setCellValue('R9',Yii::t('app/reports','Profit'));
        $objPHPExcel->getActiveSheet()->setCellValue('S9',Yii::t('app/reports','Production'));
        $objPHPExcel->getActiveSheet()->setCellValue('T9',Yii::t('app/reports','Tax'));

        $objPHPExcel->getActiveSheet()->setCellValue('U9',Yii::t('app/reports','Corr factor'));
        $objPHPExcel->getActiveSheet()->setCellValue('V9',Yii::t('app/reports','Commission'));
        $objPHPExcel->getActiveSheet()->setCellValue('W9',Yii::t('app/reports','Sale rate'));
        $objPHPExcel->getActiveSheet()->setCellValue('X9',Yii::t('app/reports','Tax rate'));

        $objPHPExcel->getActiveSheet()->setCellValue('Y9',Yii::t('app/reports','Payment calc condition'));
        $objPHPExcel->getActiveSheet()->setCellValue('Z9',Yii::t('app/reports','Condition currency'));

        $objPHPExcel->getActiveSheet()->setCellValue('AA9',Yii::t('app/reports','Currency code'));
        $objPHPExcel->getActiveSheet()->setCellValue('AB9',Yii::t('app/reports','Currency name'));
        $objPHPExcel->getActiveSheet()->setCellValue('AC9',Yii::t('app/reports','Enroll amount'));
        $objPHPExcel->getActiveSheet()->setCellValue('AD9',Yii::t('app/reports','Enroll unit name'));
        $i=10;


        foreach($data['data'] as $key=>$dt)
        {
            foreach($dt as $d)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$d['id']);
                //$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,\PHPExcel_Shared_Date::PHPToExcel($d['pay_date']+86400));
                $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getNumberFormat()->setFormatCode('DD.MM.YYYY');

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,($d['full_corp_name'] ? $d['full_corp_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,($d['prospects_name'] ? $d['prospects_name'] : 'N/A'));

                $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,isset($data['quantityHours'][$d['id']]) ? $data['quantityHours'][$d['id']]['paid'] : '');
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,isset($data['quantityHours'][$d['id']]) ? $data['quantityHours'][$d['id']]['spent'] : '');
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,isset($data['quantityHours'][$d['id']]) ? $data['quantityHours'][$d['id']]['balance'] : '');

                $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,($d['manager_name']  ? $d['manager_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,($d['sale_manager_name']  ? $d['sale_manager_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,($d['responsible_manager_name']  ? $d['responsible_manager_name'] : 'N/A'));

                $objPHPExcel->getActiveSheet()->setCellValue('K'.$i,($d['legal_name'] ? $d['legal_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$i,($d['bankName'] ? $d['bankName'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('M'.$i,($d['service_name'] ? $d['service_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$i,$d['pay_summ']);

                $objPHPExcel->getActiveSheet()->setCellValue('O'.$i,($d['code']?$d['code'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$i,isset($data['currency'][$d['id']]) ? $data['currency'][$d['id']] : '');
                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,isset($data['fullAmount'][$d['id']]) ? $data['fullAmount'][$d['id']] : '');


                $objPHPExcel->getActiveSheet()->setCellValue('R'.$i,($d['profit'] ? $d['profit'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('S'.$i,($d['production'] ? $d['production'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('T'.$i,($d['tax'] ? $d['tax'] : 'N/A'));


                $objPHPExcel->getActiveSheet()->setCellValue('U'.$i,($d['cnd_corr_factor'] ? $d['cnd_corr_factor'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('V'.$i,($d['cnd_commission'] ? $d['cnd_commission'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('W'.$i,($d['cnd_sale'] ? $d['cnd_sale'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('X'.$i,($d['cnd_tax'] ? $d['cnd_tax'] : 'N/A'));

                $objPHPExcel->getActiveSheet()->setCellValue('Y'.$i,($d['pay_cond_name']? $d['pay_cond_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('Z'.$i,isset($data['condCurr'][$d['id']]) ? $data['condCurr'][$d['id']] : 'N/A');

                $objPHPExcel->getActiveSheet()->setCellValue('AA'.$i,isset($arCond[$d['pay_cond_id']]) ? $arCond[$d['pay_cond_id']]['code'] : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('AB'.$i,isset($arCond[$d['pay_cond_id']]) ? $arCond[$d['pay_cond_id']]['name'] : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('AC'.$i,$d['enroll_amount'] ? $d['enroll_amount'] : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('AD'.$i,$d['enroll_unit_name'] ? $d['enroll_unit_name'] : 'N/A');
                $i++;
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(Yii::getAlias('@backend/web/reports/').$sFileName);


        return $sFileName;
    }

    /**
     * @param $data
     * @return null|string
     */
    protected function generateDocxDocument($data)
    {
        $template = \Yii::getAlias('@common/php_office_tmpl/').'payment_report_tpl.docx';
        $sFileName = 'payments-report-'.uniqid(time()).'.docx';
        try{
            $doc = new \PhpOffice\PhpWord\TemplateProcessor($template);
            //название клиентской систмы
            $doc->setValue('systemName',Yii::$app->name);

            //гланая страница
            $doc->setValue('Year',date('Y'));

            //страница с общей статистикой
            $doc->setValue('startDay',Yii::$app->formatter->asDate($this->dateFrom));
            $doc->setValue('endDay',Yii::$app->formatter->asDate($this->dateTo));
            $doc->setValue('currentDay',date('Y-m-d'));
            $doc->setValue('iExpenseTotal',$data['iExpenseTotal']);
            $doc->setValue('iTaxTotal',$data['iTaxTotal']);
            $doc->setValue('iProdTotal',$data['iProdTotal']);
            $doc->setValue('iProfTotal',$data['iExpenseReportsTotal']);

            $iCount = 0;
            foreach($data['data'] as $dt)
                if(is_array($dt))
                    $iCount+=count($dt);

            //таблица Рекламная сеть Яндекса
            $doc->cloneRow('cDate',$iCount);
                $iter = 1;
                foreach($data['data'] as $key=>$dt)
                    foreach($dt as $item)
                    {
                        $doc->setValue('cDate#'.$iter, Yii::$app->formatter->asDate($item['pay_date']));
                        $doc->setValue('contractor#'.$iter,($item['full_corp_name'] ? $item['full_corp_name'] : 'N/A'));
                        $doc->setValue('manager#'.$iter,($item['manager_name']  ? $item['manager_name'] : 'N/A'));
                        $doc->setValue('legalPerson#'.$iter,($item['legal_name'] ? $item['legal_name'] : 'N/A'));
                        $doc->setValue('service#'.$iter,($item['service_name'] ? $item['service_name'] : 'N/A'));
                        $doc->setValue('iSum#'.$iter,$item['pay_summ']);
                        $doc->setValue('currCode#'.$iter,($item['code']?$item['code'] : 'N/A'));
                        $doc->setValue('exRate#'.$iter,isset($data['currency'][$item['id']]) ? Yii::$app->formatter->asDecimal($data['currency'][$item['id']]) : '');
                        $doc->setValue('iTax#'.$iter, ($item['tax'] ? Yii::$app->formatter->asDecimal($item['tax']) : 'N/A'));
                        $doc->setValue('iProd#'.$iter,($item['production'] ? Yii::$app->formatter->asDecimal($item['production']) : 'N/A'));
                        $doc->setValue('iProfit#'.$iter,($item['profit'] ? Yii::$app->formatter->asDecimal($item['profit']) : 'N/A'));
                        $doc->setValue('exCondRate#'.$iter,isset($data['condCurr'][$item['id']]) ? Yii::$app->formatter->asDecimal($data['condCurr'][$item['id']]) : 'N/A');
                        $iter++;
                    }
            $doc->saveAs(Yii::getAlias('@backend/web/reports/').$sFileName);
            if(file_exists(Yii::getAlias('@backend/web/reports/').$sFileName))
                return $sFileName;

        }catch (\Exception $e)
        {
        }
        return NULL;
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