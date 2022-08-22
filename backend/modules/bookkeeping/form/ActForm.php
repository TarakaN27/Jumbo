<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.5.16
 * Time: 11.19
 */

namespace backend\modules\bookkeeping\form;


use common\components\acts\ActsDocumentsV2;
use common\components\behavior\UploadBehavior;
use common\components\customComponents\validation\ValidNumber;
use common\components\helpers\CustomHelper;
use common\models\ActImplicitPayment;
use common\models\AbstractActiveRecord;
use common\models\Acts;
use common\models\ActServices;
use common\models\ActToPayments;
use common\models\Payments;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ActForm extends Model
{
    public
        $iCUser,                //Контрагент
        $iLegalPerson,          //Юр. лицо
        $iCurr,                 //Валюта
        $arServices = [],       //Услуги
        $fAmount,               //Общая сумма акта (float)
        $arServAmount = [],     //Сумма по услугам
        $arServOrder = [],      //Порядок услуги в акте
        $arServQuantity = [],   //Кол-во по кажой услуге в акте
        $arServCurAmount = [],  //Сумма в валюте
        $arServCurId = [],   	//Ид валюты
        $arServCurDate = [],   	//Дата курса валюты
        $arTemplate = [],       //Шаблон по услугам для генерации
        $arTemplateEng = [],       //Шаблон по услугам для генерации
        $iActNumber,            //Номер акта
        $actDate,               //Дата акта
        $arPayment,             //Платежи, которые актируются
        $bCustomAct,            //Bool flag кастомный акт, без генерации
        $fCustomFileAct=0,      //Файл кастомного акта
        $sContractNumber,       //Номер Контракта
        $contractDate,          //Дата контракта
        $arHidePayments,        //Неявные платежи
        $bank,
		$bUseTax = 0,
		$bUseComission = 0,
		$bUpProcents = 0,
        $bTaxRate = NULL,
		$arServAmountEqu = [],     //Сумма по услугам эквивалентная
		$arServCurIdEqu = [],   	//Ид валюты эквивалентная
		$bTranslateAct;            //Bool flag акт с переводом

    protected
        $_customErrors = [];    //кастомные ошибки

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['fAmount',ValidNumber::className()],
            [['arServAmount','arServCurAmount','arHidePayments','arServAmountEqu'],'each','rule' => [ValidNumber::className()]],
            [[
                'iCUser','iLegalPerson','iCurr',
                'iActNumber','actDate','sContractNumber',
                'contractDate','bCustomAct', 'bTranslateAct', 'bUseComission','bUpProcents'
            ],'required'],
			[['bUseTax','bUseComission','bUpProcents'],'integer'],
			[['bTaxRate'],'required',
                'when' => function(){
                    return $this->bUseTax == 1;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#billform-busetax').val() == ".AbstractActiveRecord::YES.";
                }"
            ],
            [['bank', 'fCustomFileAct'], 'safe'],
            [['actDate'],'date','format' => 'php:d.m.Y'],
            [['arServices'],'each','rule' => ['integer']],
            [['contractDate'],'each','rule' => ['date','format' => 'php:d.m.Y']],
            ['fCustomFileAct','file','on' => ['insert'],'when' => function($model) {
                return $model->bCustomAct;
            }],
            ['fAmount','number','numberPattern' => '/^\s*[-+]?[0-9\s]*[\.,\s]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            [['arServAmount','arServAmountEqu','arServCurAmount','arServCurDate','arServCurId','arServCurIdEqu','arServOrder','arServQuantity','arPayment','arTemplate','arTemplateEng','arHidePayments','bTranslateAct'],'safe']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'iCUser' => Yii::t('app/book','CUser'),
            'iLegalPerson' => Yii::t('app/book','Legal person'),
            'iActNumber' => Yii::t('app/book','Act number'),
            'actDate' => Yii::t('app/book','Act date'),
            'iCurr' => Yii::t('app/book','Currency ID'),
            'fAmount' => Yii::t('app/book','Full amount'),
            'bCustomAct' => Yii::t('app/book','Custom act file'),
            'fCustomFileAct' => Yii::t('app/book','Custom file act'),
            'arTemplate' => Yii::t('app/book','Template act field'),
            'arTemplateEng' => Yii::t('app/book','Template act field'),
            'arHidePayments' => Yii::t('app/book','Payment hide block'),
            'bank' => Yii::t('app/book','Bank'),
			'bTranslateAct' => Yii::t('app/book','Translate act file'),
			'bUseTax' => Yii::t('app/documents','Use Vat'),
            'bTaxRate' => Yii::t('app/documents','Vat Rate'),
            'bUseComission' => Yii::t('app/documents','Use comissions'),
            'bUpProcents' => Yii::t('app/documents','Up procents'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $arParent =  parent::behaviors(); // TODO: Change the autogenerated stub
        return ArrayHelper::merge($arParent,[
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'fCustomFileAct',
                'scenarios' => ['default'],
                'path' => Acts::FILE_PATH.'/',
                'url' => ''
            ],
        ]);
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        //$this->trigger(BaseActiveRecord::EVENT_BEFORE_VALIDATE);
        return parent::beforeValidate(); // TODO: Change the autogenerated stub
    }

    public function getIsNewRecord()
    {
        return TRUE;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function makeRequest()
    {
        if(!$this->validateContractNumbers() || !$this->validateContractDate())
        {
            return FALSE;
        }
        $contractDate = $this->getContractDate();
        $contractNumber = $this->getContractNumber();
		
        if(empty($contractDate) || empty($contractNumber))
            return false;

        $transaction = Yii::$app->db->beginTransaction();
        try{

            $this->trigger(BaseActiveRecord::EVENT_BEFORE_INSERT);

            $obAct = new Acts();
            $obAct->cuser_id = $this->iCUser;
            $obAct->buser_id = Yii::$app->user->id;
            $this->actDate = date('Y-m-d',strtotime($this->actDate));
            $obAct->act_date = $this->actDate;
            $obAct->act_num = $this->iActNumber;
            $obAct->amount = $this->fAmount;
            $obAct->sent = Acts::NO;
            $obAct->lp_id = $this->iLegalPerson;
            $obAct->currency_id = $this->iCurr;
			$obAct->use_vat = $this->bUseTax;
			$obAct->vat_rate = $this->bTaxRate;
			$obAct->use_comission = $this->bUseComission;
			$obAct->up_procents = $this->bUpProcents;
            if($this->bank && isset($this->bank[$obAct->lp_id])) {
                $obAct->bank_id = $this->bank[$obAct->lp_id];
            }

            if($this->bCustomAct)
            {
                $obAct->genFile = Acts::NO;
                $obAct->file_name = $this->fCustomFileAct;
            }
            $obAct->contract_date = date('Y-m-d',strtotime($contractDate));
            $obAct->contract_num = $contractNumber;
			
            if(!$obAct->save())
                throw new ServerErrorHttpException();

            if(!$this->saveActsServices($obAct->id))
                throw new ServerErrorHttpException();

            if(!empty($this->arHidePayments))
                if(!$this->saveImplicitPayments($obAct->id))
                    throw new ServerErrorHttpException();

            if(!$this->bCustomAct)
            {
                $obActDoc = new ActsDocumentsV2($obAct->id,$this->iLegalPerson,$this->iCUser,$this->actDate,$this->iActNumber,$this->iCurr, $obAct->bank_id, $this->bTranslateAct, $obAct->use_vat, $obAct->vat_rate, $obAct->use_comission);
                $fileName = $obActDoc->generateDocument();
                if(!$fileName)
                    throw new Exception();

                $obAct->genFile = Acts::YES;
                $obAct->file_name = $fileName;
                if(!$obAct->save())
                    throw new ServerErrorHttpException();
            }

            if(!$this->paymentActivate($obAct->id))
                throw new ServerErrorHttpException();

            $this->trigger(BaseActiveRecord::EVENT_AFTER_INSERT);
            $transaction->commit();
			
            return TRUE;
        }catch(Exception $e){
			$_SESSION["error"] = $e->getMessage();
            $transaction->rollBack();
            return FALSE;
        }
    }
    
    

    /**
     * @return null
     */
    protected function getContractNumber()
    {
        if(!is_array($this->sContractNumber) || count($this->sContractNumber) === 0)
            return NULL;

        return end($this->sContractNumber);
    }

    /**
     * @return null
     */
    protected function getContractDate()
    {
        if(!is_array($this->contractDate) || count($this->contractDate) === 0)
            return NULL;

        return end($this->contractDate);
    }

    /**
     * @return bool
     */
    protected function validateContractNumbers()
    {
        if(!is_array($this->sContractNumber) || count($this->sContractNumber) === 0)
        {
            $this->_customErrors [] = 'Не задан номер контракта';
        }

        $currItem = NULL;
        $bFlag = TRUE;
        foreach ($this->sContractNumber as $value)
        {
            if(is_null($currItem))
            {
                $currItem = $value;
            }else{
                if($currItem != $value)
                {
                    $bFlag = FALSE;
                    break;
                }
            }
        }

        if(!$bFlag)
        {
            $this->_customErrors [] = 'Номера контрактов не совпадают';
        }
        return $bFlag;
    }

    /**
     * @return bool
     */
    protected function validateContractDate()
    {
        if(!is_array($this->contractDate) || count($this->contractDate) === 0)
        {
            $this->_customErrors [] = 'Не задана дата контракта';
        }

        $currItem = NULL;
        $bFlag = TRUE;
        foreach ($this->contractDate as $value)
        {
            if(is_null($currItem))
            {
                $currItem = $value;
            }else{
                if($currItem != $value)
                {
                    $bFlag = FALSE;
                    break;
                }
            }
        }

        if(!$bFlag)
        {
            $this->_customErrors [] = 'Даты контрактов не совпадают';
        }
        return $bFlag;
    }

    /**
     * @param $iActId
     * @return bool
     * @throws ServerErrorHttpException
     */
    protected function saveActsServices($iActId)
    {
        foreach ($this->arServices as $iServId)
        {
            if(
                !isset($this->arServAmount[$iServId]) ||
                !isset($this->arServQuantity[$iServId]) ||
                !isset($this->sContractNumber[$iServId]) ||
                !isset($this->contractDate[$iServId]) ||
                !isset($this->arTemplate[$iServId]) ||
                !isset($this->arTemplateEng[$iServId]) ||
                !isset($this->arServOrder[$iServId])
            )
            {
                throw new InvalidParamException();
            }
            $obActServ = new ActServices();
            $obActServ->act_id = $iActId;
            $obActServ->service_id = $iServId;
            $obActServ->amount = str_replace(",",".",$this->arServAmount[$iServId]);
            $obActServ->quantity = $this->arServQuantity[$iServId];
            $obActServ->contract_number = $this->sContractNumber[$iServId];
            $obActServ->contract_date = strtotime($this->contractDate[$iServId]);
            $obActServ->job_description = $this->arTemplate[$iServId];
            $obActServ->job_description_eng = $this->arTemplateEng[$iServId];
            $obActServ->ordering = (int)$this->arServOrder[$iServId];
			
			if(isset($this->arServCurAmount[$iServId]) && $this->arServCurAmount[$iServId]>0) {
				$obActServ->cur_amount = str_replace(",",".",$this->arServCurAmount[$iServId]);
				$obActServ->cur_id = $this->arServCurId[$iServId];
				$obActServ->cur_date = strtotime($this->arServCurDate[$iServId]);
			}
			
			if(isset($this->arServAmountEqu[$iServId]) && $this->arServAmountEqu[$iServId]>=0 && $obActServ->cur_id_equ!=2) {
				$obActServ->cur_amount_equ = str_replace(",",".",$this->arServAmountEqu[$iServId]);
				$obActServ->cur_id_equ = $this->arServCurIdEqu[$iServId];
			}

            if(!$obActServ->save())
                throw new ServerErrorHttpException();
        }
        return TRUE;
    }

    /**
     * @param $iActId
     * @return int
     * @throws \yii\db\Exception
     */
    protected function saveImplicitPayments($iActId)
    {
        $rows = [];
        foreach ($this->arHidePayments as $iPayId => $arServices)
        {
            foreach ($arServices as $iServId => $amount)
            {
                $rows[] = [
                    '',
                    $iActId,
                    $iPayId,
                    $iServId,
                    $amount,
                    time(),
                    time(),
                ];
            }
        }
        $obImplicit = new ActImplicitPayment();
        //групповое добавление
        return Yii::$app->db->createCommand()
            ->batchInsert(ActImplicitPayment::tableName(), $obImplicit->attributes(), $rows)
            ->execute();
    }

    /**
     * @param $actId
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    protected function paymentActivate($actId)
    {
        $arPaymentsId = Yii::$app->request->post('selection');
        if(empty($arPaymentsId)) {
            $this->_customErrors [] = 'Не заданы платежи';
            throw new InvalidParamException();
        }

        $arPayments = Payments::find()
            ->where([
                'id' => $arPaymentsId,
                'currency_id' => $this->iCurr,
                'act_close' => Payments::NO,
                'service_id' => $this->arServices
            ])
            ->orderBy(['pay_date' => SORT_DESC])
            ->all();
        #if(!$arPayments)
		#	$_SESSION["error2"] = 'Ошибка';
        #    throw new NotFoundHttpException();

        $arImplicit = Payments::find()
            ->where([
                'id' => $arPaymentsId,
                'currency_id' => $this->iCurr,
                'act_close' => Payments::NO,
                'hide_act_payment' => Payments::YES
            ])
            ->all();
        
        $arPayments = CustomHelper::getMapArrayObjectByAttribute($arPayments,'service_id');
        $arActPayment = ActToPayments::getRecordsByPaymentsId($arPaymentsId);
        $arAmount = $this->arServAmount;
        $arImplicitPost = $this->arHidePayments;
        $arIdsForClose = [];


        if(count($arImplicit) != 0)             //если есть неявные платежи
        {
            /** @var Payments $obImplicid */
            foreach ($arImplicit as $obImplicid)    //перебираем выбранные неявные плтаежи
            {
                if(!isset($arImplicitPost[$obImplicid->id]))        //смотрим чтобы в POST были параметры по неявному платежу
                {
                    throw new NotFoundHttpException();              //нет -- обшибка
                }else{
                    $tmpAmount = 0;                                             //проверяем, чтобы в POST была расписана вся сумма неявного платежа
                    foreach ($arImplicitPost[$obImplicid->id] as $amountItem)
                        $tmpAmount+=$amountItem;

                    if($tmpAmount != $obImplicid->pay_summ)
                        throw new ServerErrorHttpException();

                    $arServices = $arImplicitPost[$obImplicid->id];     //гасим по услугам

                        foreach ($arServices as $iServId => $amount)    //проходим по услугам и сохраняем актирование частями
                        {
                            if($amount == 0 || empty($amount))
                                continue;

                            if(!isset($arAmount[$iServId]))
                                throw new InvalidParamException();

                            $tmpAmount = (float)$arAmount[$iServId];    //получаем текущее значение суммы по услуге

                            if($tmpAmount < $amount)                    //если не хватает суммы у услуги для актирвоания, выкинем ошибку
								throw new InvalidParamException();

                            $obActPay = new ActToPayments();            //сохраняем историю актирвоания по частям
                            $obActPay->act_id = $actId;
                            $obActPay->amount = $amount;
                            $obActPay->payment_id = $obImplicid->id;
                            if(!$obActPay->save())
								throw new ServerErrorHttpException();

                            $arAmount[$iServId]-=(float)$amount;        //уменьшаем сумму по услуге
                        }
                    $arIdsForClose [] = $obImplicid->id;                //добавим id платежа в массив с закрытыми актами
                }
            }
        }

        foreach ($this->arServices as $iServId)                         //сохраняем все остальное
        {
            if(isset($arPayments[$iServId]) && isset($arAmount[$iServId]))
            {
                $tmpAmount = (float)$arAmount[$iServId];
                $arTmpPayments = $arPayments[$iServId];
                /** @var Payments $tmpPay */
                foreach ($arTmpPayments as $tmpPay)
                {
                    $pAmount = (float)$tmpPay->pay_summ;
                    if(isset($arActPayment[$tmpPay->id]))
                    {
                        /** @var ActToPayments $actpay */
                        foreach ($arActPayment[$tmpPay->id] as $actpay)
                        {
                            $pAmount-=(float)$actpay->amount;
                        }
                    }

                    if($pAmount > 0)
                    {
                        if($tmpAmount >= $pAmount)
                        {
                            $tmpAmount-=$pAmount;
                            $obActPay = new ActToPayments();
                            $obActPay->act_id = $actId;
                            $obActPay->amount = $pAmount;
                            $obActPay->payment_id = $tmpPay->id;
                            if(!$obActPay->save())
                                throw new ServerErrorHttpException();
                            $arIdsForClose [] = $tmpPay->id;
                        }else{
                            $obActPay = new ActToPayments();
                            $obActPay->act_id = $actId;
                            $obActPay->amount = $tmpAmount;
                            $obActPay->payment_id = $tmpPay->id;
                            if(!$obActPay->save())
                                throw new ServerErrorHttpException();
                        }
                    }else{
                        $arIdsForClose [] = $tmpPay->id;
                    }

                    if($tmpAmount == 0)
                        break;
                }
            }
        }

        if(!empty($arIdsForClose))
        {
            if(!Payments::updateAll(['act_close' => Payments::YES],['id' => $arIdsForClose]))
                throw new ServerErrorHttpException();
        }

        return TRUE;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setAttribute($name, $value)
    {
        $this->$name = $value;
    }
}