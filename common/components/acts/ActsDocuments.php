<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.10.15
 * Time: 11.57
 */

namespace common\components\acts;

use common\components\helpers\CustomHelper;
use common\models\Acts;
use common\models\ActsTemplate;
use common\models\CUser;
use common\models\LegalPerson;
use common\models\Services;
use PhpOffice\PhpWord\Exception\Exception;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use Gears\Pdf;

class ActsDocuments
{
	public
        $cntrDate = NULL,   // дата контракта
        $cntrNumber = NULL, // номер  контракта
        $bUseVat = FALSE,   // использовать НДС
        $actDate = NULL,    // дата акта
        $dVatRate = NULL,   // величина НДС в процентах
		$iTplID = NULL,     // ID шаблона для акта
		$iCuserID = NULL,   // ID контрагента
		$iServID = NULL,    // ID услуги
		$iActNum = NULL,    // номер акта
		$iLegPersID = NULL, // ID юр. лица
		$amount = NULL;     // сумма акта

	protected
        $fileName = NULL,
		$data = [],
		$lPInfo = [ //параметры юр лица
			'legalName' => '',
			'legalRequisites' => '',
			'site' => '',
			'email' => ''
		],
		$cntrInfo = [   //параметры контрагента
            'contrCorpName' =>'',
            'contrRequisites' =>'',
            'contrAddress' => '',
            'contrEmail' => '',
            'contrSite' => ''
		];

	CONST
		FOLDER_RIGHT = 0777; ///права на папку

    /**
     * @param $iActNum
     * @param $iTplID
     * @param $iLegPersID
     * @param $iCuserID
     * @param $iServID
     * @param $amount
     * @param $useVat
     * @param $vatRate
     */
    public function __construct($iActNum,$actDate,$iTplID,$iLegPersID,$iCuserID,$iServID,$amount,$cntrNumber,$cntrDate)
	{
		$this->iActNum = $iActNum;
        $this->actDate = $actDate;
		$this->iTplID = $iTplID;
		$this->iLegPersID = $iLegPersID;
		$this->iCuserID = $iCuserID;
		$this->iServID = $iServID;
		$this->amount = $amount;
        $this->cntrDate = $cntrDate;   // дата контракта
        $this->cntrNumber = $cntrNumber;

		if(!CustomHelper::isDirExist(Acts::FILE_PATH))    //проверяем чтобы существовала директория(если нет, то пробуем создать)
			throw new NotFoundHttpException('Folder for acts not exist!!');
	}
    
	/**
	 * @return string
	 */
	protected function generateName()
	{
		return $this->fileName = 'Act_'.uniqid();
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
	 * @return mixed|null|static
	 * @throws NotFoundHttpException
	 */
	protected function getTemplate()
	{
		/** @var ActsTemplate $obTpl */
		$obTpl = ActsTemplate::findOneByIDCached($this->iTplID);
		if(!$obTpl || !file_exists($obTpl->getFilePath()))
			throw new NotFoundHttpException('Act template not found');
		return $obTpl;
	}

	/**
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	protected function getCUserRequisites()
	{
		$obCuser = CUser::find()->with('requisites')->where(['id' => $this->iCuserID])->one();
		if(!$obCuser)
			throw new NotFoundHttpException('Contractor not found');
		return $obCuser;
	}

    /**
     * @return array
     */
    protected function getLegalPerson()
	{
		/** @var LegalPerson $obLP */
		$obLP = LegalPerson::findOneByIDCached($this->iLegPersID);

        if($obLP)   //использование НДС определяем по ЮР. лицу
        {
            $this->bUseVat = $obLP->use_vat;
            $this->dVatRate = CustomHelper::getVat();
        }

		return $this->lPInfo = [
			'legalName' => $obLP->name,
			'legalRequisites' => $obLP->doc_requisites,
			'site' => $obLP->doc_site,
			'email' => $obLP->doc_email
		];
	}

    /**
     * @return array
     */
    protected function getCuser()
    {
        $obCUser = $this->getCUserRequisites();
        if(!empty($obCUser) && is_object($obR = $obCUser->requisites))
        {
            $this->cntrInfo = [   //параметры контрагента
                'contrCorpName' => !empty($obR->corp_name) ? $obR->corp_name : $obCUser->getInfo(),
                'contrRequisites' =>'Р/сч: '.$obR->ch_account.' в '.$obR->b_name.' '.$obR->bank_address.' код '.$obR->b_code.', УНП:'.$obR->ynp,
                'contrAddress' => $obR->j_address,
                'contrEmail' => $obR->c_email,
                'contrSite' => $obR->site
            ];
        }
        return $this->cntrInfo;
    }

    /**
     * @param $obServ
     * @return array
     */
    protected function getData($obServ)
    {
        return [
            'contractDate' => $this->cntrDate,
            'contractNumber' => $this->cntrNumber,
            'actNumber' => $this->iActNum,
            'actDate' => $this->actDate,
            'n' => 1,
            'serviceName' => $obServ->name,
            'price' => $this->getPrice(),
            'amount' => $this->amount,
            'vat' => $this->getVat(),
            'vatAmount' => $this->getVatAmount(),
            'amountWithVat' => $this->amount,
            'fullAmount' => $this->amount,
            'totalAmountWords' => $this->getTotalAmountWords()
        ];
    }

    /**
     * @return string
     */
    protected function getTotalAmountWords()
    {
        $str = CustomHelper::numPropis($this->amount).' белорусских '.
            CustomHelper::ciRub($this->amount);

        if(!$this->bUseVat)
            $str.= ' без НДС';
        else
            $str.=',в т.ч.: НДС - '.CustomHelper::numPropis($this->getVatAmount()).' белорусских '.
                CustomHelper::ciRub($this->getVatAmount());

        return $str;
    }

    /**
     * @return null|string
     */
    protected function getVatAmount()
    {
        if(!$this->bUseVat)
            return '--';

        return $this->amount - $this->getPrice();
    }

    /**
     * @return null|string
     */
    protected function getVat()
    {
        if(!$this->bUseVat)
            return '--';
        else
            return $this->dVatRate;
    }

    /**
     * @return float|null
     */
    protected function getPrice()
    {
        if(!$this->bUseVat)
            return $this->amount;

        return round($this->amount/(1+$this->dVatRate/100),-3);
    }

    /**
     * @return bool|null|string
     * @throws NotFoundHttpException
     */
	public function generateDocument()
	{
        /** @var ActsTemplate $obTpl */
		$obTpl = $this->getTemplate();  //шаблон для акта
		$arLP = $this->getLegalPerson(); //данные о юр лице
        $obServ = Services::findOneByIDCached($this->iServID); //услуга
        $arCntr = $this->getCuser(); //контрагент
        $arData = $this->getData($obServ);
        $fileName = $this->generateName();
        $realPath = $this->generateRealPath($fileName.'.docx');

        try{
            $obDoc =  new \PhpOffice\PhpWord\TemplateProcessor($obTpl->getFilePath());
            $arData = ArrayHelper::merge($arLP,$arCntr ,$arData);
            foreach($arData as $key => $value)    //пишем данные
                $obDoc->setValue($key,$value);
            $obDoc->saveAs($realPath);
        }catch (Exception $e)
        {
            return FALSE;
        }
        return file_exists($realPath) ? $fileName.'.docx' : NULL;
	}

    /**
     * @return null
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return null|string
     */
    public function generatePDF()
    {
        if($docFile = $this->generateDocument()) //генерируем .docx
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
}