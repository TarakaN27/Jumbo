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
use common\components\helpers\CustomViewHelper;
use common\models\ActServices;
use common\models\ActsTemplate;
use common\models\BankDetails;
use common\models\CUser;
use common\models\CUserRequisites;
use common\models\ExchangeRates;
use common\models\LegalPerson;
use PhpOffice\PhpWord\TemplateProcessor;


use PhpOffice\PhpWord\PhpWord;


use yii\base\InvalidParamException;
use yii\web\NotFoundHttpException;
use common\models\Acts;
use yii\base\Exception;
use Gears\Pdf;
use Yii;

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
        $n2wUnitCustom = NULL,
        $obActTpl = NULL,
        $arServices = [],
        $legalPersonName,
        $legalPersonBankDetail,
        $legalPersonAddress,
        $legalPersonAddressEng,
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
        $totalAmountEqu=0,
        $totalVatAmount,
        $totalAmountWithVat=0,
        $totalAmountEquWithVat=0,
        $totalFiniteAmount=0,
        $amountInWords,
        $amountInWordsEng,
        $vatInWords,
        $bUseVat = FALSE,
        $cNotResident = FALSE,
        $vatRate,
        $contactDetail,
		$curCustom = '',
        $bankId,
        $use_comission = 0,
		$equ_text = "",
		$equ_text_2 = "",
		$cur_amount_equ,
		$cur_id_equ,
		$translateAct = FALSE;

    /**
     * ActsDocumentsV2 constructor.
     * @param $iLegalPerson
     * @param $iCUser
     * @param $actDate
     * @param $actNumber
     */
    public function __construct($iActId,$iLegalPerson,$iCUser,$actDate,$actNumber,$iCurrencyId, $bankId, $translateAct=false, $use_vat=false, $vat_rate=false, $use_comission=false)
    {
        if(empty($iActId) || empty($iLegalPerson)||empty($iCUser) || empty($actDate) || empty($actNumber) || empty($iCurrencyId)|| empty($bankId))
            throw new InvalidParamException();

        $this->iLegalPerson = $iLegalPerson;
        $this->iCUserId = $iCUser;
        $this->actDate = $actDate;
        $this->actNumber = $actNumber;
        $this->iActId = $iActId;
        $this->iCurrencyId = $iCurrencyId;
        $this->bankId = $bankId;
        $this->translateAct = $translateAct;
        $this->bUseVat = $use_vat;
        $this->vatRate = $vat_rate;
        $this->use_comission = $use_comission;
		
    }

    /**
     * @return null|string
     * @throws NotFoundHttpException
     */
    public function generateDocument()
    {
        $this->getLegalPersonAndActTpl();
        $this->getCUserDetail();
   #     $this->getContractDetail();

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
                'id','name','name_eng',
                'use_vat','docx_id','act_tpl_id','eng_act_tpl_id',
                'address','address_eng','use_vat','ynp',
                'mailing_address','telephone_number',
                'doc_email','doc_site'
            ])
            ->where(['id' => $this->iLegalPerson ])
            ->one();

        if(!$obLegalPerson)
            throw new NotFoundHttpException();

        $this->legalPersonName = $obLegalPerson->name;
        $this->legalPersonNameEng = $obLegalPerson->name_eng;
        $bank = BankDetails::findOne($this->bankId);
        if($bank)
            $this->legalPersonBankDetail = $bank->bank_details_act;
        else
            $this->legalPersonBankDetail = "";
        $this->legalPersonAddress = $obLegalPerson->address;
        $this->legalPersonAddressEng = $obLegalPerson->address_eng;
        $this->legalPersonYnp = $obLegalPerson->ynp;
        $this->legalPersonMailingAddress = $obLegalPerson->mailing_address;
        $this->legalPersonTelephoneNumber = $obLegalPerson->telephone_number;
        $this->legalPersonEmail = $obLegalPerson->doc_email;
        $this->legalPersonSite = $obLegalPerson->doc_site;
		
		if($this->bUseVat === false && $this->vatRate === false){
			$this->bUseVat = $obLegalPerson->use_vat; #Показывать ли НДС
			$this->vatRate = CustomHelper::getVat();
		}
		
		$col_act_tpl = $this->translateAct ? $obLegalPerson->eng_act_tpl_id: $obLegalPerson->act_tpl_id;

        if(empty($col_act_tpl))
            throw new NotFoundHttpException('template id not found');
				
        $this->obActTpl = ActsTemplate::findOne($col_act_tpl);
        if(!$this->obActTpl || ($this->obActTpl && !file_exists($this->obActTpl->getFilePath()))) {
            throw new NotFoundHttpException('Template not found');
        }

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

            $obAct = Acts::findOne($this->iActId);
            if (!$obAct)
                throw new NotFoundHttpException();
			
            $cuserContractDetail = $obAct->contract_num . ' от ' . \Yii::$app->formatter->asDate($obAct->contract_date);
            $cuserContractDetailEng = str_replace("Договор ","Agreement ",Yii::t('app/book',$obAct->contract_num)) . ' of ' . \Yii::$app->formatter->asDate($obAct->contract_date);
            if($obCUser->is_resident == 0){
                $this->cNotResident = true;
            }
            $cuserYnp = "";
            if($obCUser->requisites->type_id == CUserRequisites::TYPE_F_PERSON) {
                $cuserName = $obCUser->requisites->j_lname.' '.  $obCUser->requisites->j_fname . ' '. $obCUser->requisites->j_mname;
                $adsress =  $obCUser->requisites->p_address;
                $passportNumber = $obCUser->requisites->pasp_series.$obCUser->requisites->pasp_number;
                $passportDate = Yii::$app->formatter->asDate($obCUser->requisites->pasp_date);

                $passportAuth = $obCUser->requisites->pasp_auth;
                $cuserContractDetail = $obAct->contract_num . ' от ' . \Yii::$app->formatter->asDate($obAct->contract_date);
                $cuserContractDetailEng = str_replace("Договор ","Agreement ",Yii::t('app/book',$obAct->contract_num)) . ' of ' . \Yii::$app->formatter->asDate($obAct->contract_date);
                $template = "Заказчик: $cuserName<w:br/>
Адрес: $adsress<w:br/>
Паспортные данные: Номер $passportNumber выдан $passportDate, $passportAuth <w:br/>
Основание: договор $cuserContractDetail";

				$this->cuserAddress = $adsress;

            } else {
                $cuserName = !empty($obR->corp_name) ? $obR->corp_name : $obCUser->getInfo();
                $cuserBankDetail = $obR->new_ch_account . ' в ' . $obR->b_name . ' ' . $obR->bank_address . ' БИК ' . $obR->bik;
                $cuserAddress = $obR->j_address;
                $cuserEmail = $obR->c_email;
                $cuserWebsite = $obR->site;
                $cuserYnp = $obR->ynp;

                $template = "Заказчик: $cuserName, УНП: $cuserYnp<w:br/>
Р/сч: $cuserBankDetail<w:br/>
Основание: договор $cuserContractDetail<w:br/>
Юр. адрес: $cuserAddress<w:br/>
E-mail: $cuserEmail, Веб-сайт: $cuserWebsite";

				$this->cuserAddress = $cuserAddress;
            }
            $this->cuserName =$cuserName;
            $this->cuserYnp = $cuserYnp;
            $this->contactDetail = $template;
            $this->cuserContractDetail = $cuserContractDetail;
            $this->cuserContractDetailEng = $cuserContractDetailEng;
			
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
		$arCurCustom = [];
        /**
         * @var integer $key
         * @var ActServices $serv
         */
        foreach ($arServices as $key => $serv)
        {
			$serv->amount = str_replace([","," "],[".",""],$serv->amount);
			$serv->cur_amount_equ = str_replace([","," "],[".",""],$serv->cur_amount_equ);
            $amountWithVat = $serv->amount;
            $amountEquWithVat = $serv->cur_amount_equ;
            $vatRate = $this->bUseVat ? $this->vatRate : '';

            $amount = ($this->bUseVat && !$this->cNotResident) ? ($serv->amount/(1+$this->vatRate/100)) : $serv->amount;
            $amount = round($amount,2);
            $price = round($amount/$serv->quantity, 2);
			
			$amount_equ = $serv->cur_amount_equ;
			$this->cur_id_equ = $serv->cur_id_equ;
			$this->getCurrencyUnitsCustom($this->cur_id_equ);
            $vatAmount = $this->bUseVat ? round($serv->amount-$amount,2): '';
            if($this->bUseVat == 0 && $this->cNotResident){
                $vatRate = "-*";
                $vatAmount = "-*";
            }
            $arResult[] = $this->rubleModeCounting((int)$key+1,$serv,$price,$amount,$vatRate,$vatAmount,$amountWithVat,$amount_equ,$amountEquWithVat);
            $this->totalAmount+= $amount;
            $this->totalAmountEqu+= $amount_equ;
			
            if($this->bUseVat && !$this->cNotResident)
                $this->totalVatAmount = (float)$this->totalVatAmount + $vatAmount;

            $this->totalAmountWithVat+= $amountWithVat;
            $this->totalAmountEquWithVat+= $amountEquWithVat;
            $this->totalFiniteAmount+=$amountWithVat;
			
			if($serv->cur_amount>0 && $serv->cur_id>0 && $serv->cur_id!=self::BEL_RUBLE_ID) {
				$arCurCustom[] = '*'.$this->formatterHelper($serv->cur_amount).' '.$this->getCurrencyById($serv->cur_id).' по курсу НБ РБ на '.date("d.m.Y", $serv->cur_date).'г.';
			}
			
        }
		
		$this->curCustom = implode("\n",$arCurCustom);
        $this->amountInWordsMode();
		$_SESSION["doc"] = $arResult;
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
                            ' Без НДС согласно статьи 326 Налогового кодекса Республики Беларусь.';
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
                    ' Без НДС согласно статьи 326 Налогового кодекса Республики Беларусь.';

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
                        ' Без НДС согласно статьи 326 Налогового кодекса Республики Беларусь.';

                    $this->totalAmount = $this->formatterHelper($this->totalAmount);
                    $this->totalFiniteAmount = $this->formatterHelper($this->totalFiniteAmount);
                    if($this->bUseVat)
                        $this->totalVatAmount = $this->formatterHelper($this->totalVatAmount);

                    $this->totalAmountWithVat = $this->formatterHelper($this->totalAmountWithVat);
                    break;
                default:
                    break;
            }
			
			if($this->use_comission == 1 && $this->iCurrencyId == 2){
				$this->getCurrencyUnitsCustom($this->cur_id_equ);
				$totalAmountEqu = $this->formatterHelper($this->totalAmountEquWithVat);
				$totalAmountEquWords = CustomHelper::num2str(str_replace([","," "],[".",""],$totalAmountEqu),$this->n2wUnitCustom);
				$codeEqu = $this->getCurrencyById($this->cur_id_equ);
				
				$this->equ_text = "Стоимость услуг составляет сумму в белорусских рублях, эквивалентную ".$totalAmountEqu." (".$totalAmountEquWords.") ".$codeEqu.".";
				$this->equ_text_2 = "Оплата производится в белорусских рублях с пересчетом по курсу ".$codeEqu.", установленному НБРБ на дату платежа с увеличением на 5(пять) процентов к данному курсу ".$codeEqu.".";
			}
			
        else {
			
			$code = $this->getCurrencyById($this->iCurrencyId);
			
            $this->amountInWords = CustomHelper::num2str($this->totalFiniteAmount,$this->n2wUnit)." ".$code;
            $this->amountInWordsEng = CustomHelper::num2strEng($this->totalFiniteAmount, $code)." ".$code;

            $strVatAmount = '';
            if($this->bUseVat)
            {
                $strVatAmount = CustomHelper::num2str($this->totalVatAmount,$this->n2wUnit);
            }

           /* $this->vatInWords = $this->bUseVat ?
                ' в т.ч.: НДС - '.$strVatAmount :
                ' Без НДС согласно статьи 286 Налогового кодекса Республики Беларусь.';
           */

            $this->vatInWords = " <w:br/>
Сумма НДС*: Согласно п. 2 ст. 72 Договор о ЕАЭС от 29.05.2014;<w:br/>
Подп. 2 П. 28, Подп. 4 П. 29 Раздел IV Протокола о порядке взимания косвенных налогов и механизме контроля за их уплатой при экспорте и импорте товаров, выполнении работ, оказании услуг (приложение 18 к Договору о ЕАЭС от 29.05.2014)";

            $this->totalAmount = $this->formatterHelper(str_replace([",", " "],[".", ""],$this->totalAmount));

            $this->totalFiniteAmount = $this->formatterHelper(str_replace([",", " "],[".", ""],$this->totalFiniteAmount));
            if($this->bUseVat)
                $this->totalVatAmount = $this->formatterHelper(str_replace([",", " "],[".", ""],$this->totalVatAmount));

            $this->totalAmountWithVat = $this->formatterHelper(str_replace([",", " "],[".", ""],$this->totalAmountWithVat));
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
    protected function rubleModeCounting($colNum,$serv,$price,$amount,$vatRate,$vatAmount,$amountWithVat,$amount_equ=0,$amountEquWithVat=0)
    {
        $arResult = [
            'colNum' => $colNum,
            'jobName' => $serv->job_description,
            'jobNameEng' => $serv->job_description_eng,
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
                $arResult['amountEquWithVat'] =  $this->formatterHelper($amountEquWithVat);
                $arResult['amountEquWithVatWord'] = CustomHelper::num2str(str_replace([","," "],[".",""],$this->formatterHelper($amountEquWithVat)),$this->n2wUnitCustom);
                $arResult['equ'] = $this->formatterHelper($amount_equ);
				break;
            case 1:

                if($this->iCurrencyId == self::BEL_RUBLE_ID)
                {
                    $price = round($price,0);
                    $amount =round($amount,0);
                    $vatAmount = empty($vatAmount) ? $vatAmount : round($vatAmount);
                    $amountWithVat = round($amountWithVat,0);
                    $amountEquWithVat = round($amountEquWithVat,0);
                }

                $arResult['price'] = $this->iCurrencyId == 2 ?  $this->formatterHelper($price).'  ('.$this->getNewByr($price).') ' :  $this->formatterHelper($price);
                $arResult['amount'] = $this->iCurrencyId == 2 ?  $this->formatterHelper($amount).'  ('.$this->getNewByr($amount).') ' :  $this->formatterHelper($amount);
                $arResult['vatRate'] = $vatRate;
                $arResult['vatAmount'] = empty($vatAmount) ? '' : $this->iCurrencyId == 2 ?  $this->formatterHelper($vatAmount).'  ('.$this->getNewByr($vatAmount).') ' :  $this->formatterHelper($vatAmount);
                $arResult['amountWithVat'] = $this->iCurrencyId == 2 ?  $this->formatterHelper($amountWithVat).'  ('.$this->getNewByr($amountWithVat).') ' :  $this->formatterHelper($amountWithVat);
                $arResult['amountEquWithVat'] = $this->formatterHelper($amountEquWithVat);
                $arResult['amountEquWithVatWord'] = CustomHelper::num2str(str_replace([","," "],[".",""],$this->formatterHelper($amountEquWithVat)),$this->n2wUnitCustom);
				$arResult['equ'] = $this->formatterHelper($amount_equ);
				break;
            case 2:
                $arResult['price'] = $this->formatterHelper($price);
                $arResult['amount'] = $this->formatterHelper($amount);
                $arResult['vatRate'] = $vatRate;
                $arResult['vatAmount'] = empty($vatAmount) || $vatAmount == "-*"  ? $vatAmount : $this->formatterHelper($vatAmount);
                $arResult['amountWithVat'] = $this->formatterHelper($amountWithVat);
                $arResult['amountEquWithVat'] = $this->formatterHelper($amountEquWithVat);
                $arResult['amountEquWithVatWord'] = CustomHelper::num2str(str_replace([","," "],[".",""],$this->formatterHelper($amountEquWithVat)),$this->n2wUnitCustom);
				$arResult['equ'] = $this->formatterHelper($amount_equ);
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
            'legalPersonNameEng',
            'legalPersonBankDetail',
            'legalPersonAddress',
            'legalPersonAddressEng',
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
            'cuserContractDetailEng',
            'cuserAddress',
            'cuserEmail',
            'cuserWebsite',
            'cuserYnp',
            'totalAmount',
            'totalVatAmount',
            'totalAmountWithVat',
            'totalFiniteAmount',
            'amountInWords',
            'amountInWordsEng',
            'vatInWords',
            'contactDetail',
			'curCustom',
			'equ_text',
			'equ_text_2',
        ];

        try{
            if($this->iLegalPerson == 3 && $this->actDate>="2017-03-06" && $this->actDate<="2017-03-31"){
                $obDoc =  new TemplateProcessor(Yii::getAlias("@common/upload/docx_template").'/shlo_act.docx');
            }else{
                $obDoc =  new TemplateProcessor($this->obActTpl->getFilePath());
            }

			$document_with_table = new PhpWord();
			
			$tableStyle = array('borderSize' => 6,'borderColor' => '000000');
			$cellColSpan2 = array('gridSpan' => 2, 'valign' => 'center');
			$cellColSpan8 = array('gridSpan' => 8, 'valign' => 'center');
			$cellRowContinue = array('vMerge' => 'continue');
			$cellHCentered = array('align' => 'center');
			$cellHRight = array('align' => 'right');
			$cellVCentered = array('valign' => 'center');
			$fontStyle = ["name"=>"Arial", "size"=>"9"];
			$fontStyleBold = ["name"=>"Arial", "size"=>"9", "bold"=>true];
					
			$section = $document_with_table->addSection();
			
			$table = $section->addTable($tableStyle);
			
			$table->addRow(null, array('tblHeader' => true));
			$table->addCell(400, $cellVCentered)->addText("№", $fontStyle, $cellHCentered);
			$table->addCell(2600, $cellVCentered)->addText("Наименование работы (услуги)", $fontStyle, $cellHCentered);
			$table->addCell(700, $cellVCentered)->addText("Ед. изм.", $fontStyle, $cellHCentered);
			$table->addCell(600, $cellVCentered)->addText("Кол-во", $fontStyle, $cellHCentered);
			$table->addCell(1000, $cellVCentered)->addText("Цена", $fontStyle, $cellHCentered);
			$table->addCell(1000, $cellVCentered)->addText("Сумма", $fontStyle, $cellHCentered);
			$table->addCell(900, $cellVCentered)->addText("Ставка НДС,%", $fontStyle, $cellHCentered);
			$table->addCell(1000, $cellVCentered)->addText("Сумма НДС", $fontStyle, $cellHCentered);
			$table->addCell(1200, $cellVCentered)->addText('Стоимость всего с НДС по курсу НБ РБ на '.date("d.m.Y", strtotime($this->actDate)), $fontStyle, $cellHCentered);
			
			if($this->use_comission == 1){
				$table->addCell(1400, $cellVCentered)->addText("Справочно: сумма с НДС", $fontStyle, $cellHCentered);
			}
			
			$table->addRow();
			$table->addCell(null, $cellVCentered)->addText('${colNum}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${jobName}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('Усл.', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${quantity}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${price}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${amount}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${vatRate}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${vatAmount}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${amountWithVat}', $fontStyle, $cellHCentered);
			
			if($this->use_comission == 1){
				$table->addCell(null, $cellVCentered)->addText('${amountEquWithVat} (${amountEquWithVatWord})', $fontStyle, $cellHCentered);
			}			
			
			$table->addRow();
			$table->addCell(null, $cellColSpan2)->addText("ИТОГО", $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered);
			$table->addCell(null, $cellVCentered);
			$table->addCell(null, $cellVCentered);
			$table->addCell(null, $cellVCentered)->addText('${totalAmount}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered);
			$table->addCell(null, $cellVCentered)->addText('${totalVatAmount}', $fontStyle, $cellHCentered);
			$table->addCell(null, $cellVCentered)->addText('${totalAmountWithVat}', $fontStyle, $cellHCentered);
			
			if($this->use_comission == 1){
				$table->addCell(null, $cellVCentered);
			}
			
			$table->addRow();
			$table->addCell(null, $cellColSpan8)->addText("Всего (с учетом НДС):", $fontStyleBold, $cellHRight);
			$table->addCell(null, $cellVCentered)->addText('${totalFiniteAmount}', $fontStyle, $cellHCentered);
			
			if($this->use_comission == 1){
				$table->addCell(null, $cellRowContinue);
			}

			$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document_with_table, 'Word2007');
			$fullxml = $objWriter->getWriterPart('Document')->write();
			$tablexml = preg_replace('/^[\s\S]*(<w:tbl\b.*<\/w:tbl>).*/', '$1', $fullxml);
			$obDoc->setValue('table', $tablexml);
			
			
			foreach ($arItems as $item) {
                $obDoc->setValue($item, $this->$item);
            }
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
        if($obCurr->id ==3) {
            $this->n2wUnit[1] = ['российский рубль'   ,'российских рубля'   ,'российских рублей',0];
        }
    }
	
	protected function getCurrencyUnitsCustom($currId)
    {
        /** @var ExchangeRates $obCurr */
        $obCurr = ExchangeRates::find()->where(['id' => $currId])->one();
        if(!$obCurr)
            throw new NotFoundHttpException('Currency not found');

        $this->n2wUnitCustom = $obCurr->getUnitsForN2W();
        if($obCurr->id ==3) {
            $this->n2wUnitCustom[1] = ['российский рубль'   ,'российских рубля'   ,'российских рублей',0];
        }
    }
	
	protected function getCurrencyById($currID)
	{
		$obCurr = ExchangeRates::find()->where(['id' => $currID])->one();
		if(!$obCurr)
            throw new NotFoundHttpException('Currency not found');
		
		return $obCurr->code;
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

    public function generateNodeActXml($dom, $act){
        $this->getLegalPersonAndActTpl();
        $this->getCUserDetail();
        $this->getContractDetail();
        $this->getCurrencyUnits();
        $this->getServices();
        $actNode = $dom->createElement("Акт");
        $this->actNumber = '00000'.$this->actNumber;
        $this->actNumber = '0000-'.substr($this->actNumber,strlen($this->actNumber)-6);
        $actNumber =$dom->createElement('НомерАкта',$this->actNumber);
        $actDate = $dom->createElement('ДатаАкта',$this->actDate.'T00:00:00');
        $cuserName = $dom->createElement('НаименованиеКонтрагента',$this->cuserName);
        $unp = $dom->createElement('УНПКонтрагента',$this->cuserYnp);
        $contractNumber = $dom->createElement('НомерДоговора',str_replace("Договор ","", str_replace("№","",$act->contract_num)));
        $contractDate = $dom->createElement('ДатаДоговора',$act->contract_date.'T00:00:00');
        $actNode->appendChild($actNumber);
        $actNode->appendChild($actDate);
        $actNode->appendChild($cuserName);
        $actNode->appendChild($unp);
        $actNode->appendChild($contractNumber);
        $actNode->appendChild($contractDate);
        $servicesNode = $dom->createElement("СтрокиАкта");
        foreach($this->arServices as $service){
            $serviceNode = $dom->createElement("СтрокаАкта");
            $serviceName = $dom->createElement("НаименованиеУслуги",$service['jobName']);
            $serviceQuantity = $dom->createElement("Количество",$service['quantity']);
            $servicePrice = $dom->createElement("Цена",$service['price']);
            $serviceAmount = $dom->createElement("Сумма",$service['amount']);
            $serviceVatRate = $dom->createElement("СтавкаНДС",$service['vatRate']);
            $serviceVatAmount = $dom->createElement("СтоимостьСНДС",$service['amountWithVat']);
            $serviceNode->appendChild($serviceName);
            $serviceNode->appendChild($serviceNameEng);
            $serviceNode->appendChild($serviceQuantity);
            $serviceNode->appendChild($servicePrice);
            $serviceNode->appendChild($serviceAmount);
            $serviceNode->appendChild($serviceVatRate);
            $serviceNode->appendChild($serviceVatAmount);
            $servicesNode->appendChild($serviceNode);
        }
        $actNode->appendChild($servicesNode);
        return $actNode;
    }

}