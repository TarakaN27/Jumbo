<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.6.16
 * Time: 12.55
 */

namespace backend\modules\documents\form;


use yii\base\Model;
use Yii;

class BillForm extends Model
{
    public
        $iCuserId = NULL,
        $iLegalPerson = NULL,
        $iDocxTpl = NULL,
        $bUseTax = 0,
        $bTaxRate = NULL,
        $billObj = '',
        $sBayTarget = '',
        $sDescription = '',
        $sOfferContract = '',
        $arServices = [],
        $arServAmount = [],
        $fAmount = 0,
        $arServTpl = [];
    
    
    public function rules()
    {
        return [
            
        ];
    }

    public function attributeLabels()
    {
        return [
            'iCuserId' => Yii::t('app/documents','Cuser ID'),
            'iLegalPerson' => Yii::t('app/documents','Legal person'),
            'iDocxTpl' => Yii::t('app/documents','Docx Tmpl ID'),
            'bUseTax' => Yii::t('app/documents','Use Vat'),
            'bTaxRate' => Yii::t('app/documents','Vat Rate'),
            'billObj' => Yii::t('app/documents','Object Text'),
            'sBayTarget' => Yii::t('app/documents','Buy Target'),
            'sDescription' => Yii::t('app/documents','Description'),
            'sOfferContract' => Yii::t('app/documents','offer_contract'),
            'fAmount' => Yii::t('app/documents','Amount')
        ];
    }


    public function makeRequest()
    {
        
        
        
        
        
        
    }
}