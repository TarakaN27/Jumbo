<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.08.15
 */

namespace common\models\managers;


use common\components\helpers\CustomHelper;
use common\models\BillDocxTemplate;
use common\models\Bills;
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
        return 'СЧЕТ №'.$this->bill_number.' от '.$this->bill_date;
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
        /** @var LegalPerson $lPerson */
        $lPerson = $this->lPerson;
        if(!empty($lPerson))
        {
            $jPerson = $lPerson->name;
            $jPersonDetail = $lPerson->doc_requisites;
            $jPersonEmail = $lPerson->doc_email;
            $jPersonSite = $lPerson->doc_email;
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

        $obBillTpl = BillTemplate::findOne($this->bill_template);

        if(!empty($obCUser) && is_object($obR = $obCUser->requisites))
        {
            $crp = $obCUser->getInfo();
            $contractor = $crp;
            $payer = $crp.$obR->j_address;
            $bankDetail = 'Р/сч: '.$obR->ch_account.' в '.$obR->b_name.' '.$obR->bank_address.' код '.$obR->b_code.', УНП:'.$obR->ynp;
            $contractorEmail = $obR->c_email;
            $contractorSite = $obR->site;
        }


        $billVatRate  =  "Без НДС";
        $billVatSumm  =  "Без НДС";
        $billTotalVat = '';
        if($this->use_vat)
        {

            $billVatRate = round($this->vat_rate,1);
            $billPrice = round($this->amount/(1+CustomHelper::getVat()/100));
            $billVatSumm = $this->amount - $billPrice;
            $billTotalVat = $billVatSumm;
            $billSumm = $billPrice;
            $billTotalSumVat = $this->amount;
            $totalSummVat = $this->amount;
            $totalSumm = $billPrice;

            $totalSummInWords = CustomHelper::my_ucfirst(CustomHelper::numPropis($billTotalSumVat)).'белорусских '.
                CustomHelper::ciRub($billTotalSumVat) .' c НДС ' ;
        }else{
            $billSumm = $this->amount;
            $billPrice = $this->amount;
            $billTotalSumVat = $this->amount;
            $totalSummVat = $this->amount;
            $totalSumm = $this->amount;

            $totalSummInWords = CustomHelper::my_ucfirst(CustomHelper::numPropis($billTotalSumVat)).'белорусских '.
                CustomHelper::ciRub($billTotalSumVat) .' без НДС согласно статьи 286 Налогового кодекса Республики Беларусь' ;
        }

        try{

            $doc = new \PhpOffice\PhpWord\TemplateProcessor($docxTpl->getFilePath());
            $doc->setValue('jPerson',Html::encode($jPerson));
            $doc->setValue('jPersonDetail',$jPersonDetail);
            $doc->setValue('jPersonSite',$jPersonSite);
            $doc->setValue('jPersonEmail',$jPersonEmail);
            $doc->setValue('billNumber',$this->bill_number);
            $doc->setValue('billDate',$this->bill_date);
            $doc->setValue('contractor',$contractor);
            $doc->setValue('payer',$payer);
            $doc->setValue('bankDetail',$bankDetail);
            $doc->setValue('contractorEmail',$contractorEmail);
            $doc->setValue('contractorSite',$contractorSite);
            $doc->setValue('payTarget',$this->buy_target);
            $doc->setValue('billSubject',$this->object_text.' '.$this->offer_contract);
            $doc->setValue('billPrice',$billPrice);
            $doc->setValue('billSumm',$billSumm);
            $doc->setValue('billVatRate',$billVatRate);
            $doc->setValue('billVatSumm',$billVatSumm);
            $doc->setValue('billTotalSumVat',$billTotalSumVat);
            $doc->setValue('totalSumm',$totalSumm);
            $doc->setValue('totalSummVat',$totalSummVat);
            $doc->setValue('totalSummInWords',$totalSummInWords);
            $doc->setValue('description',$this->description);
            $doc->setValue('billTotalVat',$billTotalVat);


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
     * @return string
     */
    public function getManErrorsStr()
    {
        return is_array($this->_manError) ? implode(';',$this->_manError) : $this->_manError;
    }

} 