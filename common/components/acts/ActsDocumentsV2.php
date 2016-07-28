<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 26.5.16
 * Time: 16.10
 * Вторая версия класса реализующего логику формирования документа акта
 * В формировании акта используется костыль, для указания деноминированной валюты в двух видах
 * после 1.01.2017 года костыль нужно удалить, так как в документах останеться только один формат для указаная бел. валюты.
 *
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
        RUB_MODE = 2,           //0 - миллионы, 1 -- миллионы/рубли, 2- рубли после деноминации
        BEL_RUBLE_ID = 2,       //костыль указываем id бел рубля
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
        $legalPersonYnp,
        $legalPersonMailingAddress,
        $legalPersonTelephoneNumber,
        $legalPersonEmail,
        $legalPersonSite,
        $actNumber,
        $actDate,
        $cuserName,
        $cuserBankDetail,
        $cuserContractDetail,
        $cuserAddress,
        $cuserEmail,
        $cuserWebsite,
        $cuserYnp,
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
            ->select([
                'id','name','doc_requisites',
                'use_vat','docx_id','act_tpl_id',
                'address','use_vat','ynp',
                'mailing_address','telephone_number',
                'doc_email','doc_site'
            ])
            ->where(['id' => $this->iLegalPerson ])
            ->one();

        if(!$obLegalPerson)
            throw new NotFoundHttpException();

        $this->legalPersonName = $obLegalPerson->name;
        $this->legalPersonBankDetail = $obLegalPerson->doc_requisites;
        $this->legalPersonAddress = $obLegalPerson->address;
        $this->legalPersonYnp = $obLegalPerson->ynp;
        $this->legalPersonMailingAddress = $obLegalPerson->mailing_address;
        $this->legalPersonTelephoneNumber = $obLegalPerson->telephone_number;
        $this->legalPersonEmail = $obLegalPerson->doc_email;
        $this->legalPersonSite = $obLegalPerson->doc_site;

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
            $this->cuserBankDetail = $obR->ch_account.' в '.$obR->b_name.' '.$obR->bank_address.' код '.$obR->b_code;
            $this->cuserAddress = $obR->j_address;
            $this->cuserEmail = $obR->c_email;
            $this->cuserWebsite = $obR->site;
            $this->cuserYnp = $obR->ynp;
        }
        return $obCUser;
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    protected function getServices()
    {
        $arServices = ActServices::find()->where(['act_id' => $this->iActId])->orderBy(['ordering' => SORT_ASC])->all();
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
            $amount = round($amount/10000,2) * 10000;
            $price = round($amount/$serv->quantity/10000, 2)*10000;

            $vatAmount = $this->bUseVat ? round($serv->amount-$amount,2): '';

            $arResult[] = $this->rubleModeCounting((int)$key+1,$serv,$price,$amount,$vatRate,$vatAmount,$amountWithVat);
            $this->totalAmount+= $amount;
            if($this->bUseVat)
                $this->totalVatAmount = (float)$this->totalVatAmount + $vatAmount;

            $this->totalAmountWithVat+= $amountWithVat;
            $this->totalFiniteAmount+=$amountWithVat;
        }

        $this->amountInWordsMode();
        return $this->arServices = $arResult;
    }

    /**
     *
     */
    protected function amountInWordsMode()
    {
        if($this->iCurrencyId == self::BEL_RUBLE_ID)
            switch (self::RUB_MODE)
            {
                case 0:
                    $this->amountInWords =
                        CustomHelper::my_ucfirst(CustomHelper::numPropis(round($this->totalFiniteAmount))).' белорусских '. CustomHelper::ciRub(round($this->totalFiniteAmount));
                        $strVatAmount = '';
                        if($this->bUseVat)
                        {
                            $strVatAmount =
                                CustomHelper::my_ucfirst(CustomHelper::numPropis(round($this->totalVatAmount))).' белорусских '. CustomHelper::ciRub($this->totalVatAmount);
                        }

                        $this->vatInWords = $this->bUseVat ?
                            ' в т.ч.: НДС - '.$strVatAmount :
                            ' Без НДС согласно статьи 286 Налогового кодекса Республики Беларусь.';
                        $this->totalAmount = round($this->totalAmount);
                        $this->totalFiniteAmount = $this->formatterHelper($this->totalFiniteAmount);
                        if($this->bUseVat)
                            $this->totalVatAmount = $this->formatterHelper($this->totalVatAmount);

                        $this->totalAmountWithVat = $this->formatterHelper($this->totalAmountWithVat);
                    break;
                case 1:
                $this->amountInWords = CustomHelper::num2str(($this->totalFiniteAmount/10000),$this->n2wUnit);
                $strVatAmount = '';
                if($this->bUseVat)
                {
                    $strVatAmount = CustomHelper::num2str($this->totalVatAmount/10000,$this->n2wUnit);
                }

                $this->vatInWords = $this->bUseVat ?
                    ' в т.ч.: НДС - '.$strVatAmount :
                    ' Без НДС согласно статьи 286 Налогового кодекса Республики Беларусь.';

                $this->totalAmount = $this->formatterHelper($this->totalAmount).'  ('.$this->getNewByr($this->totalAmount).')';
                $this->totalFiniteAmount = $this->formatterHelper($this->totalFiniteAmount).'  ('.$this->getNewByr($this->totalFiniteAmount).')';
                if($this->bUseVat)
                    $this->totalVatAmount = $this->formatterHelper($this->totalVatAmount).'  ('.$this->getNewByr($this->totalVatAmount).')';

                $this->totalAmountWithVat = $this->formatterHelper($this->totalAmountWithVat).'  ('.$this->getNewByr($this->totalAmountWithVat).')';
                    break;
                case 2:
                    $this->amountInWords = CustomHelper::num2str($this->totalFiniteAmount,$this->n2wUnit);
                    $strVatAmount = '';
                    if($this->bUseVat)
                    {
                        $strVatAmount = CustomHelper::num2str($this->totalVatAmount,$this->n2wUnit);
                    }

                    $this->vatInWords = $this->bUseVat ?
                        ' в т.ч.: НДС - '.$strVatAmount :
                        ' Без НДС согласно статьи 286 Налогового кодекса Республики Беларусь.';

                    $this->totalAmount = $this->formatterHelper($this->totalAmount);
                    $this->totalFiniteAmount = $this->formatterHelper($this->totalFiniteAmount);
                    if($this->bUseVat)
                        $this->totalVatAmount = $this->formatterHelper($this->totalVatAmount);

                    $this->totalAmountWithVat = $this->formatterHelper($this->totalAmountWithVat);
                    break;
                default:
                    break;
            }
        else{
            $this->amountInWords = CustomHelper::num2str($this->totalFiniteAmount,$this->n2wUnit);
            $strVatAmount = '';
            if($this->bUseVat)
            {
                $strVatAmount = CustomHelper::num2str($this->totalVatAmount,$this->n2wUnit);
            }

            $this->vatInWords = $this->bUseVat ?
                ' в т.ч.: НДС - '.$strVatAmount :
                ' Без НДС согласно статьи 286 Налогового кодекса Республики Беларусь.';

            $this->totalAmount = $this->formatterHelper($this->totalAmount);

            $this->totalFiniteAmount = $this->formatterHelper($this->totalFiniteAmount);
            if($this->bUseVat)
                $this->totalVatAmount = $this->formatterHelper($this->totalVatAmount);

            $this->totalAmountWithVat = $this->formatterHelper($this->totalAmountWithVat);
        }
    }

    /**
     * @param $colNum
     * @param $serv
     * @param $price
     * @param $amount
     * @param $vatRate
     * @param $vatAmount
     * @param $amountWithVat
     * @return array
     */
    protected function rubleModeCounting($colNum,$serv,$price,$amount,$vatRate,$vatAmount,$amountWithVat)
    {
        $arResult = [
            'colNum' => $colNum,
            'jobName' => $serv->job_description,
            'quantity' => $serv->quantity
        ];

        switch (self::RUB_MODE)
        {
            case 0:
                $arResult['price'] = $this->formatterHelper($price);
                $arResult['amount'] = $this->formatterHelper($amount);
                $arResult['vatRate'] = $vatRate;
                $arResult['vatAmount'] = empty($vatAmount) ? '' : $this->formatterHelper($vatAmount);
                $arResult['amountWithVat'] =  $this->formatterHelper($amountWithVat);
                break;
            case 1:

                if($this->iCurrencyId == self::BEL_RUBLE_ID)
                {
                    $price = round($price,0);
                    $amount =round($amount,0);
                    $vatAmount = empty($vatAmount) ? $vatAmount : round($vatAmount);
                    $amountWithVat = round($amountWithVat,0);
                }

                $arResult['price'] = $this->iCurrencyId == 2 ?  $this->formatterHelper($price).'  ('.$this->getNewByr($price).') ' :  $this->formatterHelper($price);
                $arResult['amount'] = $this->iCurrencyId == 2 ?  $this->formatterHelper($amount).'  ('.$this->getNewByr($amount).') ' :  $this->formatterHelper($amount);
                $arResult['vatRate'] = $vatRate;
                $arResult['vatAmount'] = empty($vatAmount) ? '' : $this->iCurrencyId == 2 ?  $this->formatterHelper($vatAmount).'  ('.$this->getNewByr($vatAmount).') ' :  $this->formatterHelper($vatAmount);
                $arResult['amountWithVat'] = $this->iCurrencyId == 2 ?  $this->formatterHelper($amountWithVat).'  ('.$this->getNewByr($amountWithVat).') ' :  $this->formatterHelper($amountWithVat);
                break;
            case 2:
                $arResult['price'] = $this->formatterHelper($price);
                $arResult['amount'] = $this->formatterHelper($amount);
                $arResult['vatRate'] = $vatRate;
                $arResult['vatAmount'] = empty($vatAmount) ? '' : $this->formatterHelper($vatAmount);
                $arResult['amountWithVat'] = $this->formatterHelper($amountWithVat);
                break;
            default:
                break;
        }

        return $arResult;
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
        return $this->fileName = 'Act_'.$this->actNumber.'_'.str_replace('.','_',$this->actDate).'_'.$this->iLegalPerson.$this->iCUserId;
    }

    protected function getDocument()
    {
        $fileName = $this->generateName();
        $realPath = $this->generateRealPath($fileName.'.docx');
        $arItems = [
            'legalPersonName',
            'legalPersonBankDetail',
            'legalPersonAddress',
            'legalPersonYnp',
            'legalPersonMailingAddress',
            'legalPersonTelephoneNumber',
            'legalPersonEmail',
            'legalPersonSite',
            'actNumber',
            'actDate',
            'cuserName',
            'cuserBankDetail',
            'cuserContractDetail',
            'cuserAddress',
            'cuserEmail',
            'cuserWebsite',
            'cuserYnp',
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
                $iCounter++;
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

        return $this->cuserContractDetail = $obAct->contract_num.' от '.\Yii::$app->formatter->asDate($obAct->contract_date);
    }

}