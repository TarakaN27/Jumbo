<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\forms;


use common\models\CUser;
use common\models\LegalPerson;
use common\models\Payments;
use common\models\PaymentsCalculations;
use common\models\Services;
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
            [['dateFrom','dateTo'],'date','format' => 'yyyy-M-d'],
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
        $data = Payments::find()->with('calculate','cuser','legal','service');
        $data->where(
            ' pay_date > "'.strtotime($this->dateFrom.' 00:00:00 ').'"'.
            ' AND pay_date < "'.strtotime($this->dateTo.' 23:59:59').'"'
        );

        $data->andFilterWhere([
            'service_id' => $this->services,
            'cuser_id' => $this->contractor
        ]);

        $data->orderBy(Payments::tableName().'.pay_date ASC');

        $data = $data->all();

        $arResult = [
            'data' => [],
            'excelLink' => '',
            'docxLink' => '',
            'iSumTotal' => 0,
            'iProfitTotal' => 0,
            'iTaxTotal' => 0,
            'iProdTotal' => 0
        ];

        foreach($data as $dt)
        {
            $arResult['data'][date('Y-m-d',$dt->pay_date)][] = $dt;
            $arResult['iSumTotal']+= $dt->pay_summ;
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

        return $arResult;
    }

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


    protected function generateDocxDocument()
    {
        $template = \Yii::getAlias('@common/php_office_tmpl/').'google_report_tpl_1.docx';
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
            $doc->setValue('campStat',Yii::t('app/gadws',$camp['campaignState']));
            $doc->setValue('sumValue',$camp['budget']);
            $doc->setValue('restValue',$camp['cost']);
            $doc->setValue('shows',$camp['impressions']);
            $doc->setValue('clicks',$camp['clicks']);
            $doc->setValue('ctr',$camp['ctr']);

            $campStat = $this->statAdverts;

            //таблица Рекламная сеть Яндекса
            $doc->cloneRow('cDate',count($campStat));

            $totalCClicks = 0;
            $totalCShows = 0;
            $totalcSum = 0;

            foreach($campStat as $key => $item)
            {
                $iter = $key+1;
                $doc->setValue('cDate#'.$iter, $item['day']);
                $doc->setValue('cShows#'.$iter,$item['impressions']);
                $doc->setValue('cClicks#'.$iter,$item['clicks']);
                $doc->setValue('cCTR#'.$iter,$item['ctr']);
                $doc->setValue('cSum#'.$iter,$item['cost']);
                $doc->setValue('cAverage#'.$iter, ($item['clicks'] > 0 ? round($item['cost']/$item['clicks'],2) : 0));
                $doc->setValue('cPosition#'.$iter,$item['avgPosition']);
                $doc->setValue('cTimeOnSite#'.$iter,$item['avgVisitDurationSeconds']);

                $totalCClicks+=$item['clicks'];
                $totalCShows+=$item['impressions'];
                $totalcSum+=$item['cost'];
            }

            $doc->setValue('totalCShows',$totalCShows);
            $doc->setValue('totalCClicks',$totalCClicks);
            $doc->setValue('totalCSum',$totalcSum);

            $doc->saveAs(Yii::getAlias('@backend/web/reports/').$sFileName);
            if(file_exists(Yii::getAlias('@backend/web/reports/').$sFileName))
                return TRUE;

        }catch (\Exception $e)
        {
        }
        return FALSE;
    }

} 