<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.6.16
 * Time: 12.55
 */

namespace backend\modules\documents\form;

use common\components\customComponents\validation\ValidNumber;
use common\models\AbstractActiveRecord;
use common\models\Bills;
use common\models\BillServices;
use common\models\CuserBankDetails;
use common\models\LegalPerson;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use yii\web\ServerErrorHttpException;

class BillForm extends Model
{
    public
        $iCuserId = NULL,
        $curr_id = 2,
        $iLegalPerson = NULL,
        $iDocxTpl = NULL,
        $bUseTax = 0,
        $bTaxRate = NULL,
        $billObj = '',
        $sBayTarget = '',
        $sPayDate = '',
        $sDescription = '',
        $sOfferContract = '',
        $arServices = [],
        $arServAmount = [],
        $arServTitle = [],
        $arServTitleEng = [],
        $arServDesc = [],
        $arServContract = [],
        $arServOrder = [],
        $fAmount = 0,
        $arServTpl = [],
        $bTranslate = 0,
		$bank,
		$sPeriodDate = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['iCuserId','iLegalPerson','iDocxTpl','sBayTarget','sPayDate','sOfferContract','fAmount', 'curr_id'],'required'],
            [['fAmount'],ValidNumber::className()],
            ['arServAmount','each','rule' => [ValidNumber::className()]],
            [['bUseTax','bTranslate', 'curr_id'],'integer'],
            ['sDescription','trim'],
            [['bTaxRate'],'required',
                'when' => function(){
                    return $this->bUseTax == 1;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#billform-busetax').val() == ".AbstractActiveRecord::YES.";
                }"
            ],
            [['arServices','arServAmount','arServTitle','arServTitleEng','arServDesc','arServContract','arServTpl','arServOrder','sPeriodDate', 'bank'],'safe'],
            [['fAmount'],'number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'iCuserId' => Yii::t('app/documents','Cuser ID'),
			'curr_id' => Yii::t('app/book','Currency ID'),
            'iLegalPerson' => Yii::t('app/documents','Legal person'),
            'iDocxTpl' => Yii::t('app/documents','Docx Tmpl ID'),
            'bUseTax' => Yii::t('app/documents','Use Vat'),
            'bTaxRate' => Yii::t('app/documents','Vat Rate'),
            'billObj' => Yii::t('app/documents','Object Text'),
            'sBayTarget' => Yii::t('app/documents','Buy Target'),
            'sPayDate' => Yii::t('app/documents','Pay Date'),
            'sPeriodDate' => Yii::t('app/documents','Period of Service Provision'),
            'sDescription' => Yii::t('app/documents','Description'),
            'sOfferContract' => Yii::t('app/documents','offer_contract'),
            'fAmount' => Yii::t('app/documents','Amount'),
			'bank' => Yii::t('app/book','Bank'),
        ];
    }

    public function getBankId(){
        CuserBankDetails::find()->where();
    }

    /**
     * @return bool
     * @throws ServerErrorHttpException
     * @throws \yii\db\Exception
     */
    public function makeRequest()
    {
        $tr  = Yii::$app->db->beginTransaction();

        $obBill = new Bills();
        $obBill->cuser_id = $this->iCuserId;
        $obBill->l_person_id = $this->iLegalPerson;
        $obBill->manager_id = Yii::$app->user->id;
        $obBill->docx_tmpl_id = $this->iDocxTpl;
        $obBill->amount = $this->fAmount;
        $obBill->description = $this->sDescription;
        $obBill->buy_target = $this->sBayTarget;
        $obBill->bill_date = $this->sPayDate;
        $obBill->period_date = $this->sPeriodDate;
        $obBill->offer_contract = $this->sOfferContract;
        $obBill->use_vat = $this->bUseTax;
        $obBill->vat_rate = $this->bTaxRate;
        $obBill->curr_id = $this->curr_id;
        #$obBill->bank_id = LegalPerson::getBankDetailsByCUsers($this->iLegalPerson, $this->iCuserId);
		if($this->bank && isset($this->bank[$this->iLegalPerson])) {
			$obBill->bank_id = $this->bank[$this->iLegalPerson];
		}
        if(!$obBill->save()) {
            $tr->rollBack();
            return FALSE;
        }

        $rows = [];
        foreach ($this->arServices as $serv)
        {
            if(!isset(
                $this->arServAmount[$serv],
                $this->arServTpl[$serv],
                $this->arServTitle[$serv],
                $this->arServTitleEng[$serv],
                $this->arServDesc[$serv],
                $this->arServContract[$serv],
                $this->arServOrder[$serv]
            ))
                {
                    $tr->rollBack();
                    throw new InvalidParamException;
                }

            $amount = $this->arServAmount[$serv];
            $tpl = $this->arServTpl[$serv];
            $title = $this->arServTitle[$serv];
            $title_eng = $this->arServTitleEng[$serv];
            $description = $this->arServDesc[$serv];
            $offer = $this->arServContract[$serv];
            $order = $this->arServOrder[$serv];

            $rows []= [
                '',
                $obBill->id,
                $serv,
                $tpl,
                $amount,
                $title,
                $title_eng,
                $description,
                $offer,
                time(),
                time(),
                $order
            ];
        }

        if(count($rows) === 0) {
            $tr->rollBack();
            return false;
        }

        $model = new BillServices();    //пишем историю
        if(!Yii::$app->db->createCommand()
            ->batchInsert(BillServices::tableName(), $model->attributes(), $rows)
            ->execute())
        {
            $tr->rollBack();
            throw new ServerErrorHttpException;
        }

        $tr->commit();
        return $obBill->id;
    }
    
    
    public function update($model)
    {
        $tr  = Yii::$app->db->beginTransaction();

        $obBill = new Bills();
        $obBill->cuser_id = $this->iCuserId;
        $obBill->l_person_id = $this->iLegalPerson;
        $obBill->manager_id = Yii::$app->user->id;
        $obBill->docx_tmpl_id = $this->iDocxTpl;
        $obBill->amount = $this->fAmount;
        $obBill->description = $this->sDescription;
        $obBill->buy_target = $this->sBayTarget;
        $obBill->bill_date = $this->sPayDate;
        $obBill->period_date = $this->sPeriodDate;
        $obBill->offer_contract = $this->sOfferContract;
        $obBill->use_vat = $this->bUseTax;
        $obBill->vat_rate = $this->bTaxRate;
		$obBill->curr_id = $this->curr_id;
        if(!$obBill->save()) {
            $tr->rollBack();
            return FALSE;
        }

        $rows = [];
        foreach ($this->arServices as $serv)
        {
            if(!isset(
                $this->arServAmount[$serv],
                $this->arServTpl[$serv],
                $this->arServTitle[$serv],
                $this->arServTitleEng[$serv],
                $this->arServDesc[$serv],
                $this->arServContract[$serv],
                $this->arServOrder[$serv]
            ))
            {
                $tr->rollBack();
                throw new InvalidParamException;
            }

            $amount = $this->arServAmount[$serv];
            $tpl = $this->arServTpl[$serv];
            $title = $this->arServTitle[$serv];
            $title_eng = $this->arServTitleEng[$serv];
            $description = $this->arServDesc[$serv];
            $offer = $this->arServContract[$serv];
            $order = $this->arServOrder[$serv];

            $rows []= [
                '',
                $obBill->id,
                $serv,
                $tpl,
                $amount,
                $title,
                $title_eng,
                $description,
                $offer,
                time(),
                time(),
                $order
            ];
        }

        if(count($rows) === 0) {
            $tr->rollBack();
            return false;
        }

        $model = new BillServices();    //пишем историю
        if(!Yii::$app->db->createCommand()
            ->batchInsert(BillServices::tableName(), $model->attributes(), $rows)
            ->execute())
        {
            $tr->rollBack();
            throw new ServerErrorHttpException;
        }

        $tr->commit();
        return TRUE;
    }
    
    public function makeUpdate(Bills $obBill)
    {
        $tr  = Yii::$app->db->beginTransaction();
        $obBill->cuser_id = $this->iCuserId;
        $obBill->l_person_id = $this->iLegalPerson;
        $obBill->manager_id = Yii::$app->user->id;
        $obBill->docx_tmpl_id = $this->iDocxTpl;
        $obBill->amount = $this->fAmount;
        $obBill->description = $this->sDescription;
        $obBill->buy_target = $this->sBayTarget;
        $obBill->bill_date = $this->sPayDate;
        $obBill->period_date = $this->sPeriodDate;
        $obBill->offer_contract = $this->sOfferContract;
        $obBill->use_vat = $this->bUseTax;
        $obBill->vat_rate = $this->bTaxRate;
		$obBill->curr_id = $this->curr_id;
        #$obBill->bank_id = LegalPerson::getBankDetailsByCUsers($this->iLegalPerson, $this->iCuserId);
        if($this->bank && isset($this->bank[$this->iLegalPerson])) {
			$obBill->bank_id = $this->bank[$this->iLegalPerson];
		}
		if(!$obBill->save()) {
            $tr->rollBack();
            return FALSE;
        }

        BillServices::deleteAll(['bill_id' => $obBill->id]);

        $rows = [];
        foreach ($this->arServices as $serv)
        {
            if(!isset(
                $this->arServAmount[$serv],
                $this->arServTpl[$serv],
                $this->arServTitle[$serv],
                $this->arServTitleEng[$serv],
                $this->arServDesc[$serv],
                $this->arServContract[$serv],
                $this->arServOrder[$serv]
            ))
            {
                $tr->rollBack();
                throw new InvalidParamException;
            }

            $amount = $this->arServAmount[$serv];
            $tpl = $this->arServTpl[$serv];
            $title = $this->arServTitle[$serv];
            $title_eng = $this->arServTitleEng[$serv];
            $description = $this->arServDesc[$serv];
            $offer = $this->arServContract[$serv];
            $order = $this->arServOrder[$serv];

            $rows []= [
                '',
                $obBill->id,
                $serv,
                $tpl,
                $amount,
                $title,
                $title_eng,
                $description,
                $offer,
                time(),
                time(),
                $order
            ];
        }

        if(count($rows) === 0) {
            $tr->rollBack();
            return false;
        }

        $model = new BillServices();    //пишем историю
        if(!Yii::$app->db->createCommand()
            ->batchInsert(BillServices::tableName(), $model->attributes(), $rows)
            ->execute())
        {
            $tr->rollBack();
            throw new ServerErrorHttpException;
        }

        $tr->commit();
        return TRUE;
    }
}