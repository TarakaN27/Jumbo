<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 26.5.16
 * Time: 16.10
 * Вторая версия класса реализующего логику формирования документа акта
 */
namespace common\components\acts;

use common\components\helpers\CustomHelper;
use common\models\ActServices;
use common\models\ActsTemplate;
use common\models\CUser;
use common\models\ExchangeRates;
use common\models\LegalPerson;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use common\models\Acts;
use yii\base\Exception;
use Gears\Pdf;

class ActsDocumentsV2
{
    CONST
        RUB_MODE = 0,           //0 - миллионы, 1 -- миллионы/рубли, 2- рубли
        PRECISION = 4;          //точность округления

    protected
        $fileName,
        $iCurrencyId,
        $iLegalPerson,
        $iCUserId,
        $iActId,
        $n2wUnit = NULL,
        $obActTpl = NULL,
        $arServices = [],
        $legalPersonName,
        $legalPersonBankDetail,
        $legalPersonAddress,
        $actNumber,
        $actDate,
        $cuserName,
        $cuserBankDetail,
        $cuserContractDetail,
        $cuserAddress,
        $cuserEmail,
        $cuserWebsite,
        $totalAmount=0,
        $totalVatAmount,
        $totalAmountWithVat=0,
        $totalFiniteAmount=0,
        $amountInWords,
        $vatInWords,
        $bUseVat = FALSE,
        $vatRate;

    /**
     * ActsDocumentsV2 constructor.
     * @param $iLegalPerson
     * @param $iCUser
     * @param $actDate
     * @param $actNumber
     */
    public function __construct($iActId,$iLegalPerson,$iCUser,$actDate,$actNumber,$iCurrencyId)
    {
        if(empty($iActId) || empty($iLegalPerson)||empty($iCUser) || empty($actDate) || empty($actNumber) || empty($iCurrencyId))
            throw new InvalidParamException();

        $this->iLegalPerson = $iLegalPerson;
        $this->iCUserId = $iCUser;
        $this->actDate = $actDate;
        $this->actNumber = $actNumber;
        $this->iActId = $iActId;
        $this->iCurrencyId = $iCurrencyId;
    }

    /**
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function generateDocument()
    {
        $this->getLegalPersonAndActTpl();
        $this->getCUserDetail();
        $this->getContractDetail();
        $this->getCurrencyUnits();
        $this->getServices();
        return $this->generatePDF();
    }

    /**
     * Get legal person object
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function getLegalPersonAndActTpl()
    {
        /** @var LegalPerson $obLegalPerson */
        $obLegalPerson = LegalPerson::find()
            ->select(['id','name','doc_requisites','use_vat','docx_id','act_tpl_id','address','use_vat'])
            ->where(['id' => $this->iLegalPerson ])
            ->one();

        if(!$obLegalPerson)
            throw new NotFoundHttpException();
        
        $this->legalPersonName = $obLegalPerson->name;
        $this->legalPersonBankDetail = $obLegalPerson->doc_requisites;
        $this->legalPersonAddress = $obLegalPerson->address;
        $this->bUseVat = $obLegalPerson->use_vat;
        $this->vatRate = CustomHelper::getVat();

        if(empty($obLegalPerson->act_tpl_id))
            throw new NotFoundHttpException('template id not found');

        $this->obActTpl = ActsTemplate::findOne($obLegalPerson->act_tpl_id);
        if(!$this->obActTpl || ($this->obActTpl && !file_exists($this->obActTpl->getFilePath())))
            throw new NotFoundHttpException('Template not found');
        
        return $obLegalPerson;
    }

    /**
     * Get cuser detail information
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function getCUserDetail()
    {
        $obCUser = CUser::find()->with('requisites')->where(['id' => $this->iCUserId])->one();
        if(!$obCUser)
            throw new NotFoundHttpException();

        if(!empty($obCUser) && is_object($obR = $obCUser->requisites))
        {
            $this->cuserName = !empty($obR->corp_name) ? $obR->corp_name : $obCUser->getInfo();
            $this->cuserBankDetail = $obR->ch_account.' в '.$obR->b_name.' '.$obR->bank_address.' код '.$obR->b_code.', УНП:'.$obR->ynp;
            $this->cuserAddress = $obR->j_address;
            $this->cuserEmail = $obR->c_email;
            $this->cuserWebsite = $obR->site;
        }
        return $obCUser;
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    protected function getServices()
    {
        $arServices = ActServices::find()->where(['act_id' => $this->iActId])->all();
        if(!$arServices)
            throw new NotFoundHttpException();

        $arResult = [];
        /**
         * @var integer $key
         * @var ActServices $serv
         */
        foreach ($arServices as $key => $serv)
        {
            $amountWithVat = $serv->amount;
            $vatRate = $this->bUseVat ? $this->vatRate : '';
            $amount = $this->bUseVat ? ($serv->amount/(1+$this->vatRate/100)) : $serv->amount;
            $price = $amount/$serv->quantity;

            $vatAmount = $this->bUseVat ? $serv->amount-$amount: '';

            $arResult[] = [
                'colNum' => (int)$key+1,
                'jobName' => $serv->job_description,
                'quantity' => $serv->quantity,
                'price' => $this->iCurrencyId == 2 ? $price.'('.$this->getNewByr($price).')' : $price,
                'amount' => $this->iCurrencyId == 2 ? $amount.'('.$this->getNewByr($amount).')' : $amount,
                'vatRate' => $vatRate,
                'vatAmount' => empty($vatAmount) ? '' : $this->iCurrencyId == 2 ? $vatAmount.'('.$this->getNewByr($vatAmount).')' : $vatAmount,
                'amountWithVat' => $this->iCurrencyId == 2 ? $amountWithVat.'('.$this->getNewByr($amountWithVat).')' : $amountWithVat,

            ];

            $this->totalAmount+= $amount;
            if($this->bUseVat)
                $this->totalVatAmount = (float)$this->totalVatAmount + $vatAmount;

            $this->totalAmountWithVat+= $amountWithVat;
            $this->totalFiniteAmount+=$amountWithVat;
        }

        $this->amountInWords = CustomHelper::num2str($this->totalFiniteAmount,$this->n2wUnit);
        $this->vatInWords = $this->bUseVat ?
            ', в т.ч.: НДС - '.CustomHelper::num2str($this->totalVatAmount,$this->n2wUnit) :
            '. Без НДС согласно статьи 286 Налогового кодекса Республики Беларусь.';

        $this->totalAmount = $this->iCurrencyId == 2 ? $this->totalAmount.'('.$this->getNewByr($this->totalAmount).')' : $this->totalAmount;
        $this->totalAmountWithVat = $this->iCurrencyId == 2 ? $this->totalAmountWithVat.'('.$this->getNewByr($this->totalAmountWithVat).')' : $this->totalAmountWithVat;
        $this->totalFiniteAmount = $this->iCurrencyId == 2 ? $this->totalFiniteAmount.'('.$this->getNewByr($this->totalFiniteAmount).')' : $this->totalFiniteAmount;
        if($this->bUseVat)
            $this->totalVatAmount = $this->iCurrencyId == 2 ? $this->totalVatAmount.'('.$this->getNewByr($this->totalVatAmount).')' : $this->totalVatAmount;

        return $this->arServices = $arResult;
    }

    /**
     * @param $amount
     * @return string
     */
    protected function getNewByr($amount)
    {
        return ((int)($amount/10000)).' руб. '.(round((float)('0.'.$amount%10000),2,PHP_ROUND_HALF_DOWN)*100).' коп.';
    }

    /**
     * @param $name
     * @return string
     */
	protected function generateRealPath($name)
    {
        return \Yii::getAlias(Acts::FILE_PATH).'/'.$name;
    }

    /**
     * @return string
     */
    protected function generateName()
    {
        return $this->fileName = 'act_'.$this->actDate;
    }

    protected function getDocument()
    {
        $fileName = $this->generateName();
        $realPath = $this->generateRealPath($fileName.'.docx');
        $arItems = [
            'legalPersonName',
            'legalPersonBankDetail',
            'legalPersonAddress',
            'actNumber',
            'actDate',
            'cuserName',
            'cuserBankDetail',
            'cuserContractDetail',
            'cuserAddress',
            'cuserEmail',
            'cuserWebsite',
            'totalAmount',
            'totalVatAmount',
            'totalAmountWithVat',
            'totalFiniteAmount',
            'amountInWords',
            'vatInWords',
        ];

        try{
            $obDoc =  new TemplateProcessor($this->obActTpl->getFilePath());
            foreach ($arItems as $item)
                $obDoc->setValue($item,$this->$item);

            $obDoc->cloneRow('colNum',count($this->arServices));
            $iCounter = 1;
            foreach ($this->arServices as  $value)
            {
                foreach ($value as $keyItem => $val)
                    $obDoc->setValue($keyItem.'#'.$iCounter,$val);
            }
            $obDoc->saveAs($realPath);
        }catch (Exception $e)
        {
            return FALSE;
        }
        return file_exists($realPath) ? $fileName.'.docx' : NULL;
    }


    /**
     * @return null|string
     */
    protected function generatePDF()
    {
        if($docFile = $this->getDocument()) //генерируем .docx
        {
            $pdfTryPath = $this->generateRealPath($this->fileName.'.pdf'); //путь к pdf
            $docTryPath = $this->generateRealPath($docFile);
            Pdf::convert($docTryPath,$pdfTryPath); //конверитируем .docx => .pdf
            if(file_exists($pdfTryPath))
            {
                return $this->fileName.'.pdf';
            }
            @unlink($docTryPath);
        }
        return NULL;
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function getCurrencyUnits()
    {
        /** @var ExchangeRates $obCurr */
        $obCurr = ExchangeRates::find()->where(['id' => $this->iCurrencyId])->one();
        if(!$obCurr)
            throw new NotFoundHttpException('Currency not found');

        $this->n2wUnit = $obCurr->getUnitsForN2W();
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    protected function getContractDetail()
    {
        /** @var Acts $obAct */
        $obAct = Acts::findOne($this->iActId);
        if(!$obAct)
            throw new NotFoundHttpException();

        return $this->cuserContractDetail = $obAct->act_num.' от '.$obAct->act_date;
    }

}