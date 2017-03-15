<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.08.15
 */

namespace common\models\managers;


use common\components\helpers\CustomHelper;
use common\models\BankDetails;
use common\models\BillDocxTemplate;
use common\models\Bills;
use common\models\BillServices;
use common\models\BillTemplate;
use common\models\CuserServiceContract;
use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use common\models\LegalPerson;
use common\models\CUser;
use Gears\Pdf;

class BillsManager extends Bills{

    CONST
        RUBLE_MODE = 2,             //1- миллионы /2 - рубли после деноминации
        BILLS_PATH = '@common/upload/docx_bills';

    protected
        $_manError = [];

    public function getDocument($type)
    {
        if($type == self::TYPE_DOC_DOCX)
            return $this->generateDocx();

        if($type == self::TYPE_DOC_PDF)
            return $this->generatePDF();

        return NULL;
    }

    /**
     * @return string
     */
    protected function getBillName()
    {
        return 'СЧЕТ_№'.$this->bill_number.'_'.uniqid('wmc_');
    }

    /**
     * @param $name
     * @return string
     */
    protected function getTryPath($name)
    {
        return Yii::getAlias(self::BILLS_PATH).'/'.$name; //полный путь к отчету
    }

    /**
     *
     */
    protected function generateDocx()
    {
        $name = $this->getBillName().'.docx';   //название
        $tryPath = $this->getTryPath($name); //полный путь к отчету
        if($this->generateDocument($name,$tryPath))
            CustomHelper::getDocument($tryPath,$name);
        else
        {
            echo $this->getManErrorsStr();
            Yii::$app->end(500);
        }
    }

    /**
     *
     */
    protected function generatePDF()
    {
        $name = $this->getBillName();   //название
        $tryPath = $this->getTryPath($name.'.docx'); //полный путь к отчету
        if($this->generateDocument($name.'.docx',$tryPath)) //генерируем .docx
        {
            $pdfTryPath = $this->getTryPath($name.'.pdf'); //путь к pdf
            Pdf::convert($tryPath,$pdfTryPath); //конверитируем .docx => .pdf
            if(file_exists($pdfTryPath))
            {
                @unlink($tryPath);
                $dName = $this->billNameForDownload();
                CustomHelper::getDocument($pdfTryPath,$dName.'.pdf');
            }
        }
        echo 'Ошибка формирования счета';
        Yii::$app->end(500);
    }

    /**
     * @return string
     */
    protected function billNameForDownload()
    {
        return 'СЧЕТ №'.$this->bill_number.' от '.Yii::$app->formatter->asDate($this->bill_date);
    }

    protected function generateDocument($name,$tryPath)
    {

        /** @var BillDocxTemplate $docxTpl */
        $docxTpl = BillDocxTemplate::findOneByIDCached($this->docx_tmpl_id);    //находим шаблон для формирования счета
        if(empty($docxTpl) || !file_exists($docxTpl->getFilePath()))
            throw new NotFoundHttpException('Docx template not found');

        $jPerson = '';
        $jPersonDetail = '';
        $jPersonSite = '';
        $jPersonEmail = '';
        $billHint = '';
        /** @var LegalPerson $lPerson */
        $lPerson = $this->lPerson;
        if(!empty($lPerson))
        {
            $bankDetails = "";
            if($this->bank_id){
                $bank = BankDetails::findOne($this->bank_id);
                if($bank){
                    $bankDetails = $bank->bank_details;
                }
            }
            if($bank->bill_hint) {
                $prevBill = Bills::find()->where(['cuser_id'=>$this->cuser_id, 'l_person_id'=>$this->l_person_id])->andWhere(['<','bill_date',$this->bill_date])->andWhere(['<>','id',$this->id])->orderBy(['bill_date'=>SORT_DESC])->one();
                if($prevBill && $prevBill->bank_id != $this->bank_id)
                    $billHint = "ВНИМАНИЕ! Изменились банковские реквизиты";
            }
            $jPerson = $lPerson->name;
            $jPersonDetail = $bankDetails.",\nУНП:".$lPerson->ynp.".\nЮр.адрес:".$lPerson->address.
                ".\nПочт. адрес:".$lPerson->mailing_address.
                ".\nтел.:".$lPerson->telephone_number;
            $jPersonEmail = $lPerson->doc_email;
            $jPersonSite = $lPerson->doc_site;
        }

        $contractor = '';
        $payer = '';
        $bankDetail = '';
        $contractorEmail = '';
        $contractorSite = '';

        /** @var CUser $obCUser */
        $obCUser = CUser::find()->with('requisites')->where(['id' => $this->cuser_id])->one();

        /** @var CuserServiceContract $obServ */
        $obServ = CuserServiceContract::findOne(['id' => $this->cuser_id,'service_id' => $this->service_id]);

        if(!empty($obCUser) && is_object($obR = $obCUser->requisites))
        {
            $crp = $obCUser->getInfo();
            $contractor = $crp;
            $payer = $crp.$obR->j_address;
            $bankDetail = 'Р/сч: '.$obR->ch_account.' в '.$obR->b_name.' '.$obR->bank_address.' код '.$obR->b_code.', УНП:'.$obR->ynp;
            $contractorEmail = $obR->c_email;
            $contractorSite = $obR->site;
        }
        $arFields = [];
        $billVatRate  =  "Без НДС";
        $billVatSumm  =  "Без НДС";
        $billTotalSumVat = 0;
        $billTotalVat = '';
        $totalSummVat = 0;
        $totalSumm = 0;
        $tplId = false;
        if(empty($this->service_id))
        {
            $arServices = BillServices::find()->where(['bill_id' => $this->id])->orderBy(['ordering' => SORT_ASC])->all();
            if(!$arServices)
                throw new NotFoundHttpException();
            $keyCounter = 1;
            if($this->use_vat)
            {
                $billTotalVat = 0;
                /** @var BillServices $service */
                foreach ($arServices as $service)
                {
                    $tplId = $service->serv_tpl_id;
                    $service->amount = round($service->amount,2);
                    $vatAmount = round($service->amount*CustomHelper::getVat()/(100+CustomHelper::getVat()),2);
                    $price = round($service->amount -$vatAmount,2);
                    $arFields [] = [
                        'colNum' => $keyCounter,
                        'billSubject' => $service->serv_title,
                        'billPrice' => $this->amountHelperFormat($price),
                        'billSumm' => $this->amountHelperFormat($price),
                        'billVatSumm' => $this->amountHelperFormat($vatAmount),
                        'totalSummVat' => $this->amountHelperFormat($service->amount),
                        'billVatRate' => CustomHelper::getVat(),
                        'billTotalSumVat' => $this->amountHelperFormat($service->amount)
                    ];

                    $billTotalVat+= $vatAmount;
                    $totalSummVat+=$service->amount;
                    $totalSumm+=$price  ;
                    $billTotalSumVat+=$service->amount;
                    $keyCounter++;
                }
            }else{
                foreach ($arServices as $service)
                {
                    $tplId = $service->serv_tpl_id;
                    $arFields [] = [
                        'colNum' => $keyCounter,
                        'billSubject' => $service->serv_title,
                        'billPrice' => $this->amountHelperFormat($service->amount),
                        'billSumm' => $this->amountHelperFormat($service->amount),
                        'billVatSumm' => '',
                        'totalSummVat' => $this->amountHelperFormat($service->amount),
                        'billVatRate' => '',
                        'billTotalSumVat' => $this->amountHelperFormat($service->amount)
                    ];
                    $totalSummVat+=$service->amount;
                    $totalSumm+=$service->amount;
                    $billTotalSumVat+=$service->amount;
                    $keyCounter++;
                }
            }
        }else{
            $this->amount = round((float)$this->amount,2);
            if($this->use_vat)
            {
                $totalSum = $billTotalSumVat = $this->amount;
                $vatAmount = round($this->amount*CustomHelper::getVat()/(100+CustomHelper::getVat()),2);
                $price = $sum = round($this->amount -$vatAmount,2);

                $arFields [] = [
                    'colNum' => 1,
                    'billSubject' => $this->object_text,
                    'billPrice' => $this->amountHelperFormat($price),
                    'billSumm' => $this->amountHelperFormat($sum),
                    'billVatSumm' => $this->amountHelperFormat($vatAmount),
                    'totalSummVat' => $this->amountHelperFormat($totalSum),
                    'billVatRate' => CustomHelper::getVat(),
                    'billTotalSumVat' => $this->amountHelperFormat($totalSum)
                ];
                $totalSumm=$price;
                $billTotalVat = $vatAmount ;
                $totalSummVat = $totalSum;
                $billTotalSumVat = $totalSum;
            }else{
                $billTotalSumVat = $this->amount;
                $totalSummVat = $this->amount;
                $totalSumm = $this->amount;
                $arFields [] = [
                    'colNum' => 1,
                    'billSubject' => $this->object_text,
                    'billPrice' => $this->amountHelperFormat($this->amount),
                    'billSumm' => $this->amountHelperFormat($this->amount),
                    'billVatSumm' => '',
                    'totalSummVat' => $this->amountHelperFormat($this->amount),
                    'billVatRate' => '',
                    'billTotalSumVat' => $this->amountHelperFormat($this->amount)
                ];
            }
        }
        if($tplId) {
            $obBillTpl = BillTemplate::findOne($tplId);
        }else
            $obBillTpl = false;
        /*
        if($this->use_vat)
        {
            $totalSummInWords = CustomHelper::my_ucfirst(CustomHelper::numPropis((int)$billTotalSumVat)).'белорусских '.
                CustomHelper::ciRub((int)$billTotalSumVat) .' c НДС ' ;
        }else{
            $totalSummInWords = CustomHelper::my_ucfirst(CustomHelper::numPropis((int)$billTotalSumVat)).'белорусских '.
                CustomHelper::ciRub((int)$billTotalSumVat) .' без НДС согласно статьи 286 Налогового кодекса Республики Беларусь' ;
        }
        */

        $totalSummInWords =
            CustomHelper::num2str($billTotalSumVat);

        $totalSummInWords.= $this->use_vat ? ' c НДС ' : ' без НДС согласно статьи 286 Налогового кодекса Республики Беларусь';
        try{
            if($this->l_person_id == 3 && $this->bill_date>="2017-03-06" && $this->bill_date<="2017-03-31"){
                $doc = new \PhpOffice\PhpWord\TemplateProcessor(Yii::getAlias("@common/upload/docx_template").'/shlo_bill.docx');
            }else {
                $doc = new \PhpOffice\PhpWord\TemplateProcessor($docxTpl->getFilePath());
            }
            $doc->setValue('jPerson',Html::encode($jPerson));
            $doc->setValue('jPersonDetail',$jPersonDetail);
            $doc->setValue('jPersonSite',$jPersonSite);
            $doc->setValue('billHint',$billHint);
            $doc->setValue('validity', $obBillTpl?$obBillTpl->validity:"");
            $doc->setValue('jPersonEmail',$jPersonEmail);
            $doc->setValue('billNumber',$this->bill_number);
            $doc->setValue('billDate',Yii::$app->formatter->asDate($this->bill_date));
            $doc->setValue('contractor',$contractor);
            $doc->setValue('payer',$payer);
            $doc->setValue('bankDetail',$bankDetail);
            $doc->setValue('contractorEmail',$contractorEmail);
            $doc->setValue('contractorSite',$contractorSite);
            $doc->setValue('payTarget',$this->buy_target);

            $doc->cloneRow('colNum',count($arFields));                  //размножаем таблицу
            $iCounter = 1;
            foreach ($arFields as  $value)                              //пишем строки в таблицу
            {
                foreach ($value as $keyItem => $val)
                    $doc->setValue($keyItem.'#'.$iCounter,$val);

                $iCounter++;
            }

            $doc->setValue('totalSumm',$this->formatterHelper($totalSumm));
            $doc->setValue('totalSummVat',$this->amountHelperFormat($totalSummVat));
            $doc->setValue('totalSummInWords',$totalSummInWords);
            $doc->setValue('description',$this->description);
            $doc->setValue('billTotalVat',empty($billTotalVat) ? '' : $this->formatterHelper($billTotalVat));
            if(!empty($this->offer_contract))
                $doc->setValue('billOfferta','Оплата счета производится '.$this->offer_contract);

            $doc->saveAs($tryPath);

            if(file_exists($tryPath))
                return TRUE;
            else
            {
                $this->_manError[] = 'Ошибка сохранения файла.';
                return FALSE;
            }

        }catch (\Exception $e)
        {
            $this->_manError [] = 'Ошибка формирования .docx файла';
            return FALSE;
        }
    }

    /**
     * @param $amount
     * @return string
     */
    protected function amountHelperFormat($amount)
    {
        return $this->formatterHelper($amount);
    }

    /**
     * @param $amount
     * @return string
     */
    protected function formatterHelper($amount)
    {
        return \Yii::$app->formatter->asDecimal($amount,2);
    }

    /**
     * @param $amount
     * @return string
     */
    protected function getNewByr($amount)
    {
        //return ((int)($amount/10000)).' руб. '.(round((float)('0.'.$amount%10000),2,PHP_ROUND_HALF_DOWN)*100).' коп.';
        \Yii::$app->formatter->decimalSeparator = ',';
        return \Yii::$app->formatter->asDecimal($amount/10000,2);
    }

    /**
     * @return string
     */
    public function getManErrorsStr()
    {
        return is_array($this->_manError) ? implode(';',$this->_manError) : $this->_manError;
    }

}