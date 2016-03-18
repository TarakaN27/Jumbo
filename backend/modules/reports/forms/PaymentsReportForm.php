<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\forms;

use common\models\CUser;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\Payments;
use yii\base\Model;
use Yii;
use common\models\PaymentsCalculations;
use yii\db\Query;

class PaymentsReportForm extends Model{

    CONST   //типы группировки
        GROUP_BY_DATE = 1,
        GROUP_BY_MANAGER = 2,
        GROUP_BY_SERVICE = 3,
        GROUP_BY_CONTRACTOR = 4;

    public
        $groupType = self::GROUP_BY_DATE,
        $services,
        $contractor,
        $managers,
        $dateFrom,
        $generateExtendExcel,
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
            [['dateFrom','dateTo'],'date','format' => 'php:d.m.Y'],
            [['services','contractor','managers'],'safe'],
            [['generateExcel','generateDocx','groupType','generateExtendExcel'],'integer'],
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
            'services' => Yii::t('app/reports','Services'),
            'contractor' => Yii::t('app/reports','Contractor'),
            'managers' => Yii::t('app/reports','Managers'),
            'dateFrom' => Yii::t('app/reports','Date from'),
            'dateTo' => Yii::t('app/reports','Date to'),
            'generateExcel' => Yii::t('app/reports','Generate excel'),
            'generateDocx' => Yii::t('app/reports','Generate docx'),
            'groupType' => Yii::t('app/reports','Group type'),
            'generateExtendExcel' => Yii::t('app/reports','Generate extend excel')
        ];
    }

    /**
     * @return array
     */
    public static function getGroupByMap()
    {
        return [
            self::GROUP_BY_DATE => Yii::t('app/reports','Group by date'),
            self::GROUP_BY_MANAGER => Yii::t('app/reports','Group by manager'),
            self::GROUP_BY_SERVICE => Yii::t('app/reports','Group by service'),
            self::GROUP_BY_CONTRACTOR => Yii::t('app/reports','Group by contractor')
        ];
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
        $data = Payments::find();//->with('calculate','cuser','legal','service','calculate.payCond');
        //$data->joinWith('calculate');
        $data->joinWith('cuser');
        //$data->joinWith('cuser.manager');
        $data->joinWith('cuser.requisites');
        $data->joinWith('legal');
        $data->joinWith('service');
        $data->joinWith('calculate.payCond');
        $data->joinWith('payRequest.manager');
        $data->where(
            Payments::tableName().'.pay_date >= "'.strtotime($this->dateFrom.' 00:00:00 ').'"'.
            ' AND '.Payments::tableName().'.pay_date <= "'.strtotime($this->dateTo.' 23:59:59').'"'
        );

        $data->andFilterWhere([
            Payments::tableName().'.service_id' => $this->services,
            Payments::tableName().'.cuser_id' => $this->contractor,
            PaymentRequest::tableName().'.manager_id' => $this->managers
        ]);

        $data->orderBy(Payments::tableName().'.pay_date ASC');

        $data = $data->all();

        $arResult = [
            'data' => [],
            'excelLink' => '',
            'currency' => [],
            'condCurr' => [],
            'docxLink' => '',
            'iSumTotal' => 0,
            'iProfitTotal' => 0,
            'iTaxTotal' => 0,
            'iProdTotal' => 0,
            'summControll' => 0,
            'totalGroupSum' => [],
            'totalGroupProfit' => [],
            'totalGroupTax' => [],
            'totalGroupProd' => []

        ];
        $arCurr = [];
        $arCondition = [];
        $totalGroupSum = [];
        $totalGroupProfit = [];
        $totalGroupTax = [];
        $totalGroupProd = [];

        /** @var Payments $dt */
        foreach($data as $dt)
        {
            $date = date('Y-m-d',$dt->pay_date);
            $iCurr = 0;

            if(isset($arCurr[$date]) && isset($arCurr[$date][$dt->currency_id]))
            {
                $iCurr = $arCurr[$date][$dt->currency_id];
            }else{
                $iCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$dt->currency_id);
                $arCurr[$date][$dt->currency_id] = $iCurr;
            }
            $iCondCurr = 0;
            if(is_object($calc=$dt->calculate) && is_object($cond = $calc->payCond))
            {
                if(isset($arCurr[$date]) && isset($arCurr[$date][$cond->cond_currency]))
                {
                    $iCondCurr = $arCurr[$date][$cond->cond_currency];
                }else{
                    $iCondCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$cond->cond_currency);
                    $arCurr[$date][$cond->cond_currency] = $iCondCurr;
                }
                $arCondition [] = $cond->id;
            }

            $tmpCalc = $dt->calculate;

            //формируем массив с данными
            switch ($this->groupType){
                case self::GROUP_BY_DATE:
                    $arResult['data'][$date][] = $dt;
                    $totalGroupSum = $this->totalHelper($totalGroupSum,$date,$dt->pay_summ*$iCurr);
                    if(is_object($tmpCalc))
                    {
                        $totalGroupProd = $this->totalHelper($totalGroupProd,$date,$tmpCalc->production);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit,$date,$tmpCalc->profit);
                        $totalGroupTax = $this->totalHelper($totalGroupTax,$date,$tmpCalc->tax);
                    }
                    break;
                case self::GROUP_BY_MANAGER:
                    $manID = is_object($obUser = $dt->payRequest) ? is_object($obMan = $obUser->manager) ? $obMan->getFio() : 'n_a' : 'n_a';
                    $arResult['data'][$manID][] = $dt;

                    $totalGroupSum = $this->totalHelper($totalGroupSum,$manID,$dt->pay_summ*$iCurr);
                    if(is_object($tmpCalc))
                    {
                        $totalGroupProd = $this->totalHelper($totalGroupProd,$manID,$tmpCalc->production);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit,$manID,$tmpCalc->profit);
                        $totalGroupTax = $this->totalHelper($totalGroupTax,$manID,$tmpCalc->tax);
                    }
                    break;
                case self::GROUP_BY_SERVICE:
                    $obServ = $dt->service;
                    $servName = is_object($obServ) ? $obServ->name : 'n_a';
                    $arResult['data'][$servName][] = $dt;

                    $totalGroupSum = $this->totalHelper($totalGroupSum,$servName,$dt->pay_summ*$iCurr);
                    if(is_object($tmpCalc))
                    {
                        $totalGroupProd = $this->totalHelper($totalGroupProd,$servName,$tmpCalc->production);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit,$servName,$tmpCalc->profit);
                        $totalGroupTax = $this->totalHelper($totalGroupTax,$servName,$tmpCalc->tax);
                    }
                    break;
                case self::GROUP_BY_CONTRACTOR:
                    $obCuser = $dt->cuser;
                    $corpName = is_object($obCuser) ? $obCuser->getInfoWithSite() : 'n_a';
                    $arResult['data'][$corpName][] = $dt;

                    $totalGroupSum = $this->totalHelper($totalGroupSum,$corpName,$dt->pay_summ*$iCurr);
                    if(is_object($tmpCalc))
                    {
                        $totalGroupProd = $this->totalHelper($totalGroupProd,$corpName,$tmpCalc->production);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit,$corpName,$tmpCalc->profit);
                        $totalGroupTax = $this->totalHelper($totalGroupTax,$corpName,$tmpCalc->tax);
                    }
                    break;
                default:
                    break;
            }

            $arResult['iSumTotal']+= ($dt->pay_summ*$iCurr);
            $arResult['currency'][$dt->id] = $iCurr;
            $arResult['condCurr'][$dt->id] = $iCondCurr;
            if(is_object($tmpCalc))
            {
                $arResult['iProfitTotal']+= $tmpCalc->profit;
                $arResult['iTaxTotal']+= $tmpCalc->tax;
                $arResult['iProdTotal']+= $tmpCalc->production;
            }

        }

        $arResult['totalGroupSum'] = $totalGroupSum;
        $arResult['totalGroupProfit'] = $totalGroupProfit;
        $arResult['totalGroupTax'] = $totalGroupTax;
        $arResult['totalGroupProd'] = $totalGroupProd;

        if($this->generateExcel)
            $arResult['excelLink'] = $this->generateExcelDocument($arResult);

        if($this->generateDocx)
            $arResult['docxLink'] = $this->generateDocxDocument($arResult);

        if($this->generateExtendExcel)
            $arResult['excelExtendLink'] = $this->generateExtendExcelDocument($arResult,$arCondition);

        $arResult['summControll'] = $arResult['iSumTotal'] - ($arResult['iProfitTotal']+$arResult['iTaxTotal']+$arResult['iProdTotal']);

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

        $sFileName = 'payments-report-'.uniqid(time()).'.xlsx';

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
            ->setCellValue('B4', $data['iSumTotal'])
            ->setCellValue('A5', 'Общая прибыль:')
            ->setCellValue('B5', $data['iProfitTotal'])
            ->setCellValue('A6', 'Общий налог:')
            ->setCellValue('B6', $data['iTaxTotal'])
            ->setCellValue('A7', 'Общие производственные затраты:')
            ->setCellValue('B7', $data['iProdTotal']);


        $objPHPExcel->getActiveSheet()
            ->setCellValue('A9', 'Детализация платежей:');

        $objPHPExcel->getActiveSheet()->setCellValue('A9',Yii::t('app/reports','Payments date'));
        $objPHPExcel->getActiveSheet()->setCellValue('B9',Yii::t('app/reports','Contractor'));
        $objPHPExcel->getActiveSheet()->setCellValue('C9',Yii::t('app/reports','Payment owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9',Yii::t('app/reports','Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('E9',Yii::t('app/reports','Service'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9',Yii::t('app/reports','Payment sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9',Yii::t('app/reports','Payment currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('H9',Yii::t('app/reports','Exchange currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('I9',Yii::t('app/reports','Profit'));
        $objPHPExcel->getActiveSheet()->setCellValue('J9',Yii::t('app/reports','Production'));
        $objPHPExcel->getActiveSheet()->setCellValue('K9',Yii::t('app/reports','Tax'));
        $objPHPExcel->getActiveSheet()->setCellValue('L9',Yii::t('app/reports','Payment calc condition'));
        $objPHPExcel->getActiveSheet()->setCellValue('M9',Yii::t('app/reports','Condition currency'));

        $i=10;

            foreach($data['data'] as $key=>$dt)
            {
                foreach($dt as $d)
                {
                    $cuser=$d->cuser;
                    $calc=$d->calculate;
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,Yii::$app->formatter->asDate($d->pay_date));
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,is_object($cuser) ? $cuser->getInfo() : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,is_object($req = $d->payRequest)&&is_object($obMan = $req->manager) ? $obMan->getFio() : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,is_object($lp=$d->legal) ? $lp->name : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,is_object($serv=$d->service) ? $serv->name : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$d->pay_summ);

                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,is_object($curr = $d->currency) ? $curr->code : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,isset($data['currency'][$d->id]) ? $data['currency'][$d->id] : '');

                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,is_object($calc) ? $calc->profit : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,is_object($calc) ? $calc->production : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$i,is_object($calc) ? $calc->tax : 'N/A');

                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$i,is_object($calc) ? (is_object($cond = $calc->payCond) ? $cond->name : 'N/A') : 'N/A');
                    $objPHPExcel->getActiveSheet()->setCellValue('M'.$i,isset($data['condCurr'][$d->id]) ? $data['condCurr'][$d->id] : 'N/A');
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
            ->setCellValue('B4', $data['iSumTotal'])
            ->setCellValue('A5', 'Общая прибыль:')
            ->setCellValue('B5', $data['iProfitTotal'])
            ->setCellValue('A6', 'Общий налог:')
            ->setCellValue('B6', $data['iTaxTotal'])
            ->setCellValue('A7', 'Общие производственные затраты:')
            ->setCellValue('B7', $data['iProdTotal']);


        $objPHPExcel->getActiveSheet()
            ->setCellValue('A9', 'Детализация платежей:');

        $objPHPExcel->getActiveSheet()->setCellValue('A9',Yii::t('app/reports','Payments ID'));
        $objPHPExcel->getActiveSheet()->setCellValue('B9',Yii::t('app/reports','Payments date'));
        $objPHPExcel->getActiveSheet()->setCellValue('C9',Yii::t('app/reports','Contractor'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9',Yii::t('app/reports','Payment owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('E9',Yii::t('app/reports','Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9',Yii::t('app/reports','Service'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9',Yii::t('app/reports','Payment sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('H9',Yii::t('app/reports','Payment currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('I9',Yii::t('app/reports','Exchange currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('J9',Yii::t('app/reports','Profit'));
        $objPHPExcel->getActiveSheet()->setCellValue('K9',Yii::t('app/reports','Production'));
        $objPHPExcel->getActiveSheet()->setCellValue('L9',Yii::t('app/reports','Tax'));

        $objPHPExcel->getActiveSheet()->setCellValue('M9',Yii::t('app/reports','Corr factor'));
        $objPHPExcel->getActiveSheet()->setCellValue('N9',Yii::t('app/reports','Commission'));
        $objPHPExcel->getActiveSheet()->setCellValue('O9',Yii::t('app/reports','Sale rate'));
        $objPHPExcel->getActiveSheet()->setCellValue('P9',Yii::t('app/reports','Tax rate'));

        $objPHPExcel->getActiveSheet()->setCellValue('Q9',Yii::t('app/reports','Payment calc condition'));
        $objPHPExcel->getActiveSheet()->setCellValue('R9',Yii::t('app/reports','Condition currency'));

        $objPHPExcel->getActiveSheet()->setCellValue('S9',Yii::t('app/reports','Currency code'));
        $objPHPExcel->getActiveSheet()->setCellValue('T9',Yii::t('app/reports','Currency name'));
        $i=10;

        foreach($data['data'] as $key=>$dt)
        {
            foreach($dt as $d)
            {
                $cuser=$d->cuser;
                /** @var PaymentsCalculations $calc */
                $calc=$d->calculate;

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$d->id);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,Yii::$app->formatter->asDate($d->pay_date));
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,is_object($cuser) ? $cuser->getInfo() : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,is_object($req = $d->payRequest)&&is_object($obMan = $req->manager) ? $obMan->getFio() : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,is_object($lp=$d->legal) ? $lp->name : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,is_object($serv=$d->service) ? $serv->name : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$d->pay_summ);

                $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,is_object($curr = $d->currency) ? $curr->code : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,isset($data['currency'][$d->id]) ? $data['currency'][$d->id] : '');

                $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,is_object($calc) ? $calc->profit : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$i,is_object($calc) ? $calc->production : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$i,is_object($calc) ? $calc->tax : 'N/A');

                $objPHPExcel->getActiveSheet()->setCellValue('M'.$i,is_object($calc) ? $calc->cnd_corr_factor : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('N'.$i,is_object($calc) ? $calc->cnd_commission : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('O'.$i,is_object($calc) ? $calc->cnd_sale : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('P'.$i,is_object($calc) ? $calc->cnd_tax : 'N/A');

                $objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,is_object($calc) ? (is_object($cond = $calc->payCond) ? $cond->name : 'N/A') : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('R'.$i,isset($data['condCurr'][$d->id]) ? $data['condCurr'][$d->id] : 'N/A');

                $objPHPExcel->getActiveSheet()->setCellValue('S'.$i,is_object($calc) && isset($arCond[$calc->pay_cond_id]) ? $arCond[$calc->pay_cond_id]['code'] : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('T'.$i,is_object($calc) && isset($arCond[$calc->pay_cond_id]) ? $arCond[$calc->pay_cond_id]['name'] : 'N/A');

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
            $doc->setValue('iSumTotal',$data['iSumTotal']);
            $doc->setValue('iTaxTotal',$data['iTaxTotal']);
            $doc->setValue('iProdTotal',$data['iProdTotal']);
            $doc->setValue('iProfTotal',$data['iProfitTotal']);

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
                        $doc->setValue('cDate#'.$iter, Yii::$app->formatter->asDate($item->pay_date));
                        $doc->setValue('contractor#'.$iter,is_object($cuser=$item->cuser) ? $cuser->getInfo() : 'N/A');
                        $doc->setValue('manager#'.$iter,is_object($req = $item->payRequest)&&is_object($obMan = $req->manager) ? $obMan->getFio() : 'N/A');
                        $doc->setValue('legalPerson#'.$iter,is_object($lp=$item->legal) ? $lp->name : 'N/A');
                        $doc->setValue('service#'.$iter,is_object($serv=$item->service) ? $serv->name : 'N/A');
                        $doc->setValue('iSum#'.$iter,$item->pay_summ);
                        $doc->setValue('currCode#'.$iter,is_object($curr = $item->currency) ? $curr->code : 'N/A');
                        $doc->setValue('exRate#'.$iter,isset($data['currency'][$item->id]) ? Yii::$app->formatter->asDecimal($data['currency'][$item->id]) : '');
                        $doc->setValue('iTax#'.$iter, is_object($calc=$item->calculate) ? $calc->tax : 'N/A');
                        $doc->setValue('iProd#'.$iter,is_object($calc=$item->calculate) ? $calc->production : 'N/A');
                        $doc->setValue('iProfit#'.$iter,is_object($calc=$item->calculate) ? $calc->tax : 'N/A');
                        $doc->setValue('exCondRate#'.$iter,isset($data['condCurr'][$item->id]) ? Yii::$app->formatter->asDecimal($data['condCurr'][$item->id]) : 'N/A');
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

} 