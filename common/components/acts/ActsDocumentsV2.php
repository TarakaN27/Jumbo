<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 26.5.16
 * Time: 16.10
 * Вторая версия класса реализующего логику формирования документа акта
 */

namespace common\components\acts;


use common\models\ActsTemplate;
use common\models\CUser;
use common\models\LegalPerson;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class ActsDocumentsV2
{
    protected
        $iLegalPerson,
        $iCUserId,
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
        $totalAmount,
        $totalTaxAmount,
        $totalAmountWithTax,
        $totalFiniteAmount,
        $amountInWords,
        $taxInWords;

    /**
     * ActsDocumentsV2 constructor.
     * @param $iLegalPerson
     * @param $iCUser
     * @param $actDate
     * @param $actNumber
     */
    public function __construct($iLegalPerson,$iCUser,$actDate,$actNumber)
    {
        if(empty($iLegalPerson)||empty($iCUser) || empty($actDate) || empty($actNumber))
            throw new InvalidParamException();

        $this->iLegalPerson = $iLegalPerson;
        $this->iCUserId = $iCUser;
        $this->actDate = $actDate;
        $this->actNumber = $actNumber;
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
            ->select(['id','name','doc_requisites','use_vat','docx_id','act_tpl_id','address'])
            ->where(['id' => $this->iLegalPerson ])
            ->one();

        if(!$obLegalPerson)
            throw new NotFoundHttpException();
        
        $this->legalPersonName = $obLegalPerson->name;
        $this->legalPersonBankDetail = $obLegalPerson->doc_requisites;
        $this->legalPersonAddress = $obLegalPerson->address;

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
        $obCuser = CUser::find()->with('requisites')->where(['id' => $this->iCUserId])->one();
        if(!$obCuser)
            throw new NotFoundHttpException();

        if(!empty($obCUser) && is_object($obR = $obCUser->requisites))
        {
            $this->cuserName = !empty($obR->corp_name) ? $obR->corp_name : $obCUser->getInfo();
            $this->cuserBankDetail = $obR->ch_account.' в '.$obR->b_name.' '.$obR->bank_address.' код '.$obR->b_code.', УНП:'.$obR->ynp;
            $this->cuserAddress = $obR->j_address;
            $this->cuserEmail = $obR->c_email;
            $this->cuserWebsite = $obR->site;
        }
        return $obCuser;
    }
    
    



    public function generateDocument()
    {
        $this->getLegalPersonAndActTpl();


    }










}