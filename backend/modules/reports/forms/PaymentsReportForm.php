<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\forms;

use common\models\ExchangeCurrencyHistory;
use common\models\Payments;
use yii\base\Model;
use Yii;

class PaymentsReportForm extends Model{

    public
        $services,
        $contractor,
       // $managers,
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
            [['dateFrom','dateTo'],'date','format' => 'php:d.m.Y'],
            [['services','contractor'],'safe'],
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
            'services' => Yii::t('app/reports','Services'),
            'contractor' => Yii::t('app/reports','Contractor'),
         //   'managers' => Yii::t('app/reports','Managers'),
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
        $data = Payments::find();//->with('calculate','cuser','legal','service','calculate.payCond');
        $data->joinWith('calculate');
        $data->joinWith('cuser');
        $data->joinWith('legal');
        $data->joinWith('service');
        $data->joinWith('calculate.payCond');
        $data->where(
            ' pay_date > "'.strtotime($this->dateFrom.' 00:00:00 ').'"'.
            ' AND pay_date < "'.strtotime($this->dateTo.' 23:59:59').'"'
        );

        $data->andFilterWhere([
            Payments::tableName().'.service_id' => $this->services,
            Payments::tableName().'.cuser_id' => $this->contractor
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
            'summControll' => 0
        ];
        $arCurr = [];
        $arCondCurr = [];
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
            }

            $arResult['data'][$date][] = $dt;
            $arResult['iSumTotal']+= ($dt->pay_summ*$iCurr);
            $arResult['currency'][$dt->id] = $iCurr;
            $arResult['condCurr'][$dt->id] = $iCondCurr;
            if(is_object($tmp = $dt->calculate))
            {
                $arResult['iProfitTotal']+=$tmp->profit;
                $arResult['iTaxTotal']+=$tmp->tax;
                $arResult['iProdTotal']+=$tmp->production;
            }
        }

        if($this->generateExcel)
            $arResult['excelLink'] = $this->generateExcelDocument($arResult);

        if($this->generateDocx)
            $arResult['docxLink'] = $this->generateDocxDocument($arResult);

        $arResult['summControll'] = $arResult['iSumTotal'] - ($arResult['iProfitTotal']+$arResult['iTaxTotal']+$arResult['iProdTotal']);

        return $arResult;
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
            ->setCellValue('B2', $this->dateFrom)
            ->setCellValue('A3', 'Конечная дата:')
            ->setCellValue('B3', $this->dateTo)
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
        $objPHPExcel->getActiveSheet()->setCellValue('C9',Yii::t('app/reports','Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9',Yii::t('app/reports','Service'));
        $objPHPExcel->getActiveSheet()->setCellValue('E9',Yii::t('app/reports','Payment sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9',Yii::t('app/reports','Profit'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9',Yii::t('app/reports','Production'));
        $objPHPExcel->getActiveSheet()->setCellValue('H9',Yii::t('app/reports','Tax'));
        $i=10;
        foreach($data['data'] as $key=>$dt)
        {
            foreach($dt as $d)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$key);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,is_object($cuser=$d->cuser) ? $cuser->getInfo() : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,is_object($lp=$d->legal) ? $lp->name : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,is_object($serv=$d->service) ? $serv->name : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$d->pay_summ);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,is_object($calc=$d->calculate) ? $calc->profit : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,is_object($calc=$d->calculate) ? $calc->production : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,is_object($calc=$d->calculate) ? $calc->tax : 'N/A');
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
            $doc->setValue('startDay',$this->dateFrom);
            $doc->setValue('endDay',$this->dateTo);
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
                    $doc->setValue('cDate#'.$iter, $key);
                    $doc->setValue('contractor#'.$iter,is_object($cuser=$item->cuser) ? $cuser->getInfo() : 'N/A');
                    $doc->setValue('legalPerson#'.$iter,is_object($lp=$item->legal) ? $lp->name : 'N/A');
                    $doc->setValue('service#'.$iter,is_object($serv=$item->service) ? $serv->name : 'N/A');
                    $doc->setValue('iSum#'.$iter,$item->pay_summ);
                    $doc->setValue('iTax#'.$iter, is_object($calc=$item->calculate) ? $calc->tax : 'N/A');
                    $doc->setValue('iProd#'.$iter,is_object($calc=$item->calculate) ? $calc->production : 'N/A');
                    $doc->setValue('iProfit#'.$iter,is_object($calc=$item->calculate) ? $calc->tax : 'N/A');

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