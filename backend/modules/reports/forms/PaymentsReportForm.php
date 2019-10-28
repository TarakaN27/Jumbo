<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 03.08.15
 */

namespace backend\modules\reports\forms;

use backend\models\BUser;
use common\components\helpers\PhpOfficeHelper;
use common\models\Acts;
use common\models\ActToPayments;
use common\models\CUser;
use common\models\CuserProspects;
use common\models\CuserQuantityHour;
use common\models\CUserRequisites;
use common\models\EnrollmentRequest;
use common\models\ExchangeCurrencyHistory;
use common\models\ExchangeRates;
use common\models\LegalPerson;
use common\models\PaymentCondition;
use common\models\PaymentRequest;
use common\models\Payments;
use common\models\PaymentsSale;
use common\models\Services;
use common\models\UnitsEnroll;
use yii\base\Model;
use Yii;
use common\models\PaymentsCalculations;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class PaymentsReportForm extends Model
{

    CONST   //типы группировки
        GROUP_BY_DATE = 1,
        GROUP_BY_MANAGER = 2,
        GROUP_BY_SERVICE = 3,
        GROUP_BY_CONTRACTOR = 4;

    public
        $groupType = self::GROUP_BY_DATE,
        $services,
        $contractor,
        $managers,
        $dateFrom,
        $generateExtendExcel,
        $generateExcel,
        $generateDocx,
        $dateTo,
        $showWithoutSale;

    protected
        $arPaymentsInByr = [],      //платежи в белорусских рублях
        $arSales = [];              //продажи

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['dateFrom', 'dateTo'], 'required'],
            [['dateFrom', 'dateTo'], 'date', 'format' => 'php:d.m.Y'],
            [['services', 'contractor', 'managers'], 'safe'],
            [['generateExcel', 'generateDocx', 'groupType', 'generateExtendExcel', 'showWithoutSale'], 'integer'],
            [['dateFrom', 'dateTo'], 'validatePeriodDate'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatePeriodDate($attribute, $params)
    {
        if (strtotime($this->dateTo) < strtotime($this->dateFrom))
            $this->addError($attribute, Yii::t('app/reports', 'Date to must be more than date from'));
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'services' => Yii::t('app/reports', 'Services'),
            'contractor' => Yii::t('app/reports', 'Contractor'),
            'managers' => Yii::t('app/reports', 'Managers'),
            'dateFrom' => Yii::t('app/reports', 'Date from'),
            'dateTo' => Yii::t('app/reports', 'Date to'),
            'generateExcel' => Yii::t('app/reports', 'Generate excel'),
            'generateDocx' => Yii::t('app/reports', 'Generate docx'),
            'groupType' => Yii::t('app/reports', 'Group type'),
            'generateExtendExcel' => Yii::t('app/reports', 'Generate extend excel'),
            'showWithoutSale' => Yii::t('app/reports', 'Show without sale')
        ];
    }

    /**
     * @return array
     */
    public static function getGroupByMap()
    {
        $arGroup = [
            self::GROUP_BY_DATE => Yii::t('app/reports', 'Group by date'),
            self::GROUP_BY_MANAGER => Yii::t('app/reports', 'Group by manager'),
            self::GROUP_BY_SERVICE => Yii::t('app/reports', 'Group by service'),
            self::GROUP_BY_CONTRACTOR => Yii::t('app/reports', 'Group by contractor')
        ];

        if (!Yii::$app->user->can('adminRights'))
            unset($arGroup[self::GROUP_BY_MANAGER]);

        return $arGroup;
    }


    /**
     * @return string
     */
    public function getGroupByStr()
    {
        $tmp = self::getGroupByMap();
        return isset($tmp[$this->groupType]) ? $tmp[$this->groupType] : 'N/A';
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = Payments::find();//->with('calculate','cuser','legal','service','calculate.payCond');
        $arSelect = [
            Payments::tableName() . '.id',
            Payments::tableName() . '.cuser_id',
            Payments::tableName() . '.legal_id',
            Payments::tableName() . '.service_id',
            Payments::tableName() . '.pay_summ',
            Payments::tableName() . '.currency_id',
            Payments::tableName() . '.pay_date',
            Payments::tableName() . '.prequest_id',
            PaymentsCalculations::tableName() . '.pay_cond_id',
            PaymentsCalculations::tableName() . '.tax',
            PaymentsCalculations::tableName() . '.profit',
            PaymentsCalculations::tableName() . '.production',
            PaymentsCalculations::tableName() . '.cnd_corr_factor',
            PaymentsCalculations::tableName() . '.cnd_commission',
            PaymentsCalculations::tableName() . '.cnd_sale',
            PaymentsCalculations::tableName() . '.cnd_tax',
            CUserRequisites::tableName() . '.corp_name',
            CUserRequisites::tableName() . '.j_fname',
            CUserRequisites::tableName() . '.j_lname',
            CUserRequisites::tableName() . '.j_mname',
            CUserRequisites::tableName() . '.type_id',
            CUser::tableName() . '.requisites_id',
            CUser::tableName() . '.prospects_id',
            CUser::tableName() . '.manager_id',
            ExchangeRates::tableName() . '.code',
            ExchangeRates::tableName() . '.name as curr_name',
            ExchangeRates::tableName() . '.nbrb_rate',
            LegalPerson::tableName() . '.name as legal_name',
            Services::tableName() . '.name as service_name',
            PaymentCondition::tableName() . '.name as pay_cond_name',
            PaymentCondition::tableName() . '.corr_factor as pc_corr_factor',
            PaymentCondition::tableName() . '.commission  as pc_commission',
            PaymentCondition::tableName() . '.`sale`  AS pc_sale ',
            PaymentCondition::tableName() . '.tax  as pc_tax',
            PaymentCondition::tableName() . '.currency_id  as pc_currency_id',
            PaymentCondition::tableName() . '.cond_currency  as cond_currency',
            PaymentRequest::tableName() . '.manager_id as preq_man_id',
            'saleBuser.fname as sfname',
            'saleBuser.lname as slname',
            'saleBuser.mname as smname',
            'bankDetails.name as bankName',
            'managerBuser.fname',
            'managerBuser.lname',
            'managerBuser.mname',
        ];
        $data->joinWith('calculate');
        $data->joinWith('cuser');
        $data->joinWith('currency');
        //$data->joinWith('cuser.manager');
        $data->joinWith('cuser.requisites');
        if ($this->generateExtendExcel) {
            $data->joinWith('cuser.quantityHour');
            $data->joinWith('cuser.prospects');
            $data->joinWith('cuser.manager manager');
            array_push($arSelect, CuserProspects::tableName() . '.name as prospects_name');
            array_push($arSelect, CuserQuantityHour::tableName() . '.cuser_id as quant_user');
            array_push($arSelect, CuserQuantityHour::tableName() . '.hours');
            array_push($arSelect, CuserQuantityHour::tableName() . '.spent_time');
            $arSelect['managerFname'] = 'manager.fname';
            $arSelect['managerLname'] = 'manager.lname';
            $arSelect['managerMname'] = 'manager.mname';
            $data->joinWith('enrollRequest');
            $data->joinWith('enrollRequest.unitEnroll');
            $data->andWhere(EnrollmentRequest::tableName() . '.parent_id is null');
            array_push($arSelect, EnrollmentRequest::tableName() . '.amount as enroll_amount');
            array_push($arSelect, UnitsEnroll::tableName() . '.name as enroll_unit_name');

        }
        $data->joinWith('legal');
        $data->joinWith('service');
        $data->joinWith('calculate.payCond');
        $data->joinWith('sale.buser saleBuser');
        $data->joinWith('payRequest.manager managerBuser');
        $data->joinWith('payRequest.bankDetails bankDetails');
        $data->select($arSelect);
        $data->andWhere(
            Payments::tableName() . '.pay_date >= "' . strtotime($this->dateFrom . ' 00:00:00 ') . '"' .
            ' AND ' . Payments::tableName() . '.pay_date <= "' . strtotime($this->dateTo . ' 23:59:59') . '"'
        );
        $data->andFilterWhere([
            Payments::tableName() . '.service_id' => $this->services,
            Payments::tableName() . '.cuser_id' => $this->contractor,
        ]);
        //если массив то значит это смотрит админ, ему показываем только те платежи у которых владелец выбранный менеджер,
        //иначе покажем менеджеру все платежи которые он должен видеть
        if ($this->managers && !is_array($this->managers)) {
            $cuserIdSales = PaymentsSale::find()->select(['cuser_id'])->where(['buser_id' => $this->managers])->asArray()->all();
            if ($cuserIdSales) {
                $cuserIdSales = ArrayHelper::getColumn($cuserIdSales, 'cuser_id');
                $data->andWhere(['or', [PaymentRequest::tableName() . '.manager_id' => $this->managers], [CUser::tableName() . '.manager_id' => $this->managers], [Payments::tableName() . '.cuser_id' => $cuserIdSales]]);
            } else
                $data->andWhere(['or', [PaymentRequest::tableName() . '.manager_id' => $this->managers], [CUser::tableName() . '.manager_id' => $this->managers], [Payments::tableName() . '.cuser_id' => $cuserIdSales]]);
        } else {
            $data->andFilterWhere([
                PaymentRequest::tableName() . '.manager_id' => $this->managers
            ]);
        }


        $data->orderBy(Payments::tableName() . '.pay_date ASC');
        $data = $data->createCommand()->queryAll();

        $paymentIds = array_unique(ArrayHelper::getColumn($data, 'id'));
        $actedPayment = ActToPayments::find()->select([
            'payment_id',
            new Expression('SUM(' . ActToPayments::tableName() . '.amount) as amount'),
            Acts::tableName() . '.act_date'])
            ->joinWith('act', false)->where(['payment_id' => $paymentIds])
            ->groupBy(['payment_id'])
            ->andWhere(
                Acts::tableName() . '.act_date >= "' . date("Y-m-d",strtotime($this->dateFrom)) . '"' .
                ' AND ' . Acts::tableName() . '.act_date <= "' . date("Y-m-d", strtotime($this->dateTo)) . '"'
            )
            ->indexBy('payment_id')
            ->asArray()
            ->all();

        $paymentsOtherPeriod = ActToPayments::find()
            ->select(Payments::tableName().'.*')
            ->addSelect(ActToPayments::tableName().'.amount')
            ->joinWith('act',false)
            ->joinWith('payment', false)
            ->andWhere(Acts::tableName() . '.act_date >= "' . date("Y-m-d",strtotime($this->dateFrom)) . '"' .
            ' AND ' . Acts::tableName() . '.act_date <= "' . date("Y-m-d", strtotime($this->dateTo)) . '"')
            ->andWhere(Payments::tableName() . '.pay_date < "' . strtotime($this->dateFrom . ' 00:00:00 ') . '"' .
                 ' OR ' . Payments::tableName() . '.pay_date > "' . strtotime($this->dateTo . ' 23:59:59') . '"')
            ->asArray()
            ->all();
        $arCurrIds = array_unique(ArrayHelper::getColumn($paymentsOtherPeriod, 'currency_id'));
        $dates = array_unique(ArrayHelper::getColumn($paymentsOtherPeriod, 'pay_date'));
        $obECH = new ExchangeCurrencyHistory();
        if (!empty($arCurrIds))
            $arCurrInBur = $obECH->getCurrencyInByrForDates($dates, $arCurrIds);
        else
            $arCurrInBur = [];
        $totalSumOtherPeriod = 0;
        foreach($paymentsOtherPeriod as $item){
            if (!isset($arCurrInBur[$item['currency_id']], $arCurrInBur[$item['currency_id']][date('Y-m-d', $item['pay_date'])]))
                throw new NotFoundHttpException('Exchange rate for currency ' . $item['currency_id'] . ' at  ' . date('Y-m-d', $item['pay_date']) . ' not found.');
            $iCurr = $arCurrInBur[$item['currency_id']][date('Y-m-d', $item['pay_date'])];
            $totalSumOtherPeriod += $item['amount'] * $iCurr;
        }


        $arResult = [
            'data' => [],
            'excelLink' => '',
            'currency' => [],
            'condCurr' => [],
            'docxLink' => '',
            'iSumTotal' => 0,
            'iProfitTotal' => 0,
            'iTaxTotal' => 0,
            'iProdTotal' => 0,
            'summControll' => 0,
            'totalGroupSum' => [],
            'totalGroupProfit' => [],
            'totalGroupTax' => [],
            'totalGroupProd' => [],
            'fullAmount' => [],
            'quantityHours' => []
        ];
        $arCurr = [];
        $arCondition = [];
        $totalGroupSum = [];
        $totalGroupActedSum = [];
        $totalGroupProfit = [];
        $totalGroupTax = [];
        $totalGroupProd = [];

        // курсы обмена валют за период

        $obECH = new ExchangeCurrencyHistory();
        $arCurrIds = array_unique(ArrayHelper::getColumn($data, 'currency_id'));
        if (!empty($arCurrIds))
            $arCurrInBur = $obECH->getCurrencyInByrForPeriod(strtotime($this->dateFrom), strtotime($this->dateTo), $arCurrIds);
        else
            $arCurrInBur = [];
        /** @var Payments $dt */
        foreach ($data as $dt) {
            $date = date('Y-m-d', $dt['pay_date']);
            $iCurr = 0;
            $dt['full_corp_name'] = false;
            if ($dt['requisites_id']) {
                $dt['full_corp_name'] = CUserRequisites::getCorpNameByDataArray($dt);
            }

            $dt['responsible_manager_name'] = false;
            if ($this->generateExtendExcel) {
                if (($dt['managerFname'] || $dt['managerLname'] || $dt['managerMname'])) {
                    $dt['responsible_manager_name'] = trim($dt['managerLname'] . ' ' . $dt['managerFname'] . ' ' . $dt['managerMname']);
                } else
                    $dt['responsible_manager_name'] = 'N/A';
            }

            $dt['manager_name'] = false;
            if ($dt['preq_man_id'] && ($dt['lname'] || $dt['fname'] || $dt['mname'])) {
                $dt['manager_name'] = trim($dt['lname'] . ' ' . $dt['fname'] . ' ' . $dt['mname']);
            } else
                $dt['manager_name'] = 'N/A';

            $dt['sale_manager_name'] = false;
            if ($dt['slname'] || $dt['sfname'] || $dt['smname']) {
                $dt['sale_manager_name'] = trim($dt['slname'] . ' ' . $dt['sfname'] . ' ' . $dt['smname']);
            } else
                $dt['sale_manager_name'] = 'N/A';
            /*
            if(isset($arCurr[$date]) && isset($arCurr[$date][$dt->currency_id]))
            {
                $iCurr = $arCurr[$date][$dt->currency_id];
            }else{
                $iCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate($date,$dt->currency_id);
                $arCurr[$date][$dt->currency_id] = $iCurr;
            }
            */
            if (!isset($arCurrInBur[$dt['currency_id']], $arCurrInBur[$dt['currency_id']][$date]))
                throw new NotFoundHttpException('Exchange rate for currency ' . $dt['currency_id'] . ' at  ' . $date . ' not found.');

            $iCurr = $arCurrInBur[$dt['currency_id']][$date];


            $iCondCurr = 0;
            if ($dt['pay_cond_id'] && ($dt['pay_cond_name'])) {
                if (isset($arCurr[$date]) && isset($arCurr[$date][$dt['cond_currency']])) {
                    $iCondCurr = $arCurr[$date][$dt['cond_currency']];
                } else {
                    $iCondCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate($date, $dt['cond_currency']);
                    $arCurr[$date][$dt['cond_currency']] = $iCondCurr;
                }
                $arCondition [] = $dt['pay_cond_id'];
            }

            if($actedPayment[$dt['id']]) {
                $dt['acted_sum'] = $actedPayment[$dt['id']]['amount'];
            } else{
                $dt['acted_sum'] = 0;
            }

            //формируем массив с данными
            switch ($this->groupType) {
                case self::GROUP_BY_DATE:
                    $arResult['data'][$date][] = $dt;
                    $totalGroupSum = $this->totalHelper($totalGroupSum, $date, $dt['pay_summ'] * $iCurr);
                    if(isset($actedPayment[$dt['id']])) {
                        $totalGroupActedSum = $this->totalHelper($totalGroupActedSum, $date, $actedPayment[$dt['id']]['amount'] * $iCurr);
                    }
                    if ($dt['pay_cond_id']) {
                        $totalGroupProd = $this->totalHelper($totalGroupProd, $date, $dt['production']);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit, $date, $dt['profit']);
                        $totalGroupTax = $this->totalHelper($totalGroupTax, $date, $dt['tax']);
                    }
                    break;
                case self::GROUP_BY_MANAGER:
                    if ($dt['manager_name'])
                        $manID = $dt['manager_name'];
                    else
                        $manID = 'n_a';

                    $arResult['data'][$manID][] = $dt;

                    $totalGroupSum = $this->totalHelper($totalGroupSum, $manID, $dt['pay_summ'] * $iCurr);
                    if(isset($actedPayment[$dt['id']])) {
                        $totalGroupActedSum = $this->totalHelper($totalGroupActedSum, $manID, $actedPayment[$dt['id']]['amount'] * $iCurr);
                    }
                    if ($dt['pay_cond_id']) {
                        $totalGroupProd = $this->totalHelper($totalGroupProd, $manID, $dt['production']);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit, $manID, $dt['profit']);
                        $totalGroupTax = $this->totalHelper($totalGroupTax, $manID, $dt['tax']);
                    }
                    break;
                case self::GROUP_BY_SERVICE:
                    if ($dt['service_name'])
                        $servName = $dt['service_name'];
                    else
                        $servName = 'n_a';

                    $arResult['data'][$servName][] = $dt;

                    $totalGroupSum = $this->totalHelper($totalGroupSum, $servName, $dt['pay_summ'] * $iCurr);
                    if(isset($actedPayment[$dt['id']])) {
                        $totalGroupActedSum = $this->totalHelper($totalGroupActedSum, $servName, $actedPayment[$dt['id']]['amount'] * $iCurr);
                    }
                    if ($dt['pay_cond_id']) {
                        $totalGroupProd = $this->totalHelper($totalGroupProd, $servName, $dt['production']);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit, $servName, $dt['profit']);
                        $totalGroupTax = $this->totalHelper($totalGroupTax, $servName, $dt['tax']);
                    }
                    break;
                case self::GROUP_BY_CONTRACTOR:
                    if ($dt['requisites_id']) {
                        $corpName = CUserRequisites::getCorpNameWithSiteByDataArray($dt);
                    }
                    $arResult['data'][$corpName][] = $dt;

                    $totalGroupSum = $this->totalHelper($totalGroupSum, $corpName, $dt['pay_summ'] * $iCurr);
                    if(isset($actedPayment[$dt['id']])) {
                        $totalGroupActedSum = $this->totalHelper($totalGroupActedSum, $totalGroupSum, $actedPayment[$dt['id']]['amount'] * $iCurr);
                    }
                    if ($dt['pay_cond_id']) {
                        $totalGroupProd = $this->totalHelper($totalGroupProd, $corpName, $dt['production']);
                        $totalGroupProfit = $this->totalHelper($totalGroupProfit, $corpName, $dt['profit']);
                        $totalGroupTax = $this->totalHelper($totalGroupTax, $corpName, $dt['tax']);
                    }
                    break;
                default:
                    break;
            }
            $this->arPaymentsInByr[$dt['id']] = (float)$dt['pay_summ'] * $iCurr;              //соберем платежи в
            $arResult['iSumTotal'] += ($dt['pay_summ'] * $iCurr);
            $arResult['iActedSumTotal'] += ($dt['acted_sum'] * $iCurr);
            $arResult['currency'][$dt['id']] = $iCurr;
            $arResult['condCurr'][$dt['id']] = $iCondCurr;
            $arResult['fullAmount'][$dt['id']] = $dt['pay_summ'] * $iCurr;

            /**  @var CuserQuantityHour $obQuant */
            if ($this->generateExtendExcel && $dt['requisites_id'] && $dt['quant_user']) {
                $arResult['quantityHours'][$dt['id']] = [
                    'paid' => $dt['hours'],
                    'spent' => $dt['spent_time'],
                    'balance' => $dt['hours'] - $dt['spent_time']
                ];
            }

            if ($dt['pay_cond_id']) {
                $arResult['iProfitTotal'] += $dt['profit'];
                $arResult['iTaxTotal'] += $dt['tax'];
                $arResult['iProdTotal'] += $dt['production'];
            }

        }
        unset($data);
        if ($this->showWithoutSale) {
            $this->getSales();                          //получаем продажи
            $saleInfo = $this->getSaleInfoAmount();                 //получаем рассчеты без продаж
            $arResult['saleAmount'] = $saleInfo['saleAmount'];
            $arResult['paymentWithoutSale'] = $saleInfo['paymentWithoutSale'];
        } else {
            $arResult['saleAmount'] = NULL;
            $arResult['paymentWithoutSale'] = NULL;
        }

        $arResult['totalGroupSum'] = $totalGroupSum;
        $arResult['totalGroupActedSum'] = $totalGroupActedSum;
        $arResult['totalGroupProfit'] = $totalGroupProfit;
        $arResult['totalGroupTax'] = $totalGroupTax;
        $arResult['totalGroupProd'] = $totalGroupProd;
        $arResult['totalSumOtherPeriod'] = $totalSumOtherPeriod;

        if ($this->generateExcel)
            $arResult['excelLink'] = $this->generateExcelDocument($arResult);

        /*        if($this->generateDocx)
                    $arResult['docxLink'] = $this->generateDocxDocument($arResult);
        */
        if ($this->generateExtendExcel)
            $arResult['excelExtendLink'] = $this->generateExtendExcelDocument($arResult, $arCondition);

        $arResult['summControll'] = $arResult['iSumTotal'] - ($arResult['iProfitTotal'] + $arResult['iTaxTotal'] + $arResult['iProdTotal']);
        return $arResult;
    }

    /**
     * @param $arArray
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function totalHelper($arArray, $key, $value)
    {
        if (isset($arArray[$key]))
            $arArray[$key] += $value;
        else
            $arArray[$key] = $value;

        return $arArray;
    }

    /**
     * @param $data
     * @return null|string
     */
    protected function generateExcelDocument($data)
    {

        if (empty($data))
            return NULL;

        $sFileName = 'payments-report-' . uniqid(time()) . '.xlsx';

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->user->id)
            ->setTitle(Yii::t('app/reports', 'Payments report'))
            ->setSubject(Yii::t('app/reports', 'Payments report'));

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A1', 'Отчет по платежам')
            ->setCellValue('A2', 'Начальная дата:')
            ->setCellValue('B2', Yii::$app->formatter->asDate($this->dateFrom))
            ->setCellValue('A3', 'Конечная дата:')
            ->setCellValue('B3', Yii::$app->formatter->asDate($this->dateTo))
            ->setCellValue('A4', 'Общая сумма платежей:')
            ->setCellValue('B4', $data['iSumTotal'])
            ->setCellValue('A5', 'Общая прибыль:')
            ->setCellValue('B5', $data['iProfitTotal'])
            ->setCellValue('A6', 'Общий налог:')
            ->setCellValue('B6', $data['iTaxTotal'])
            ->setCellValue('A7', 'Общие производственные затраты:')
            ->setCellValue('B7', $data['iProdTotal']);


        $objPHPExcel->getActiveSheet()
            ->setCellValue('A9', 'Детализация платежей:');

        $objPHPExcel->getActiveSheet()->setCellValue('A9', Yii::t('app/reports', 'Payments date'));
        $objPHPExcel->getActiveSheet()->setCellValue('B9', Yii::t('app/reports', 'Contractor'));
        $objPHPExcel->getActiveSheet()->setCellValue('C9', Yii::t('app/reports', 'Payment owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9', Yii::t('app/reports', 'Sale owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('E9', Yii::t('app/reports', 'Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9', Yii::t('app/reports', 'Service'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9', Yii::t('app/reports', 'Payment sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('H9', Yii::t('app/reports', 'Payment currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('I9', Yii::t('app/reports', 'Exchange currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('J9', Yii::t('app/reports', 'Profit'));
        $objPHPExcel->getActiveSheet()->setCellValue('K9', Yii::t('app/reports', 'Production'));
        $objPHPExcel->getActiveSheet()->setCellValue('L9', Yii::t('app/reports', 'Tax'));
        $objPHPExcel->getActiveSheet()->setCellValue('M9', Yii::t('app/reports', 'Payment calc condition'));
        $objPHPExcel->getActiveSheet()->setCellValue('N9', Yii::t('app/reports', 'Condition currency'));

        $i = 10;

        foreach ($data['data'] as $key => $dt) {
            foreach ($dt as $d) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, Yii::$app->formatter->asDate($d['pay_date']));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, ($d['full_corp_name'] ? $d['full_corp_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($d['manager_name'] ? $d['manager_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($d['sale_manager_name'] ? $d['sale_manager_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, ($d['legal_name'] ? $d['legal_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, ($d['service_name'] ? $d['service_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $d['pay_summ']);

                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, ($d['code'] ? $d['code'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, isset($data['currency'][$d['id']]) ? $data['currency'][$d['id']] : '');

                $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, ($d['profit'] ? $d['profit'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, ($d['production'] ? $d['production'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, ($d['tax'] ? $d['tax'] : 'N/A'));

                $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, ($d['pay_cond_name'] ? $d['pay_cond_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, isset($data['condCurr'][$d['id']]) ? $data['condCurr'][$d['id']] : 'N/A');
                $i++;
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(Yii::getAlias('@backend/web/reports/') . $sFileName);

        return $sFileName;
    }

    /**
     * @param $data
     * @param array $arCondIDs
     * @return string
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function generateExtendExcelDocument($data, $arCondIDs = [])
    {
        if (empty($data))
            return NULL;

        $arCondIDs = array_unique($arCondIDs);
        $arCondTmp = (new Query())
            ->select(['cond.id', 'ex.code', 'ex.name'])
            ->from(PaymentCondition::tableName() . ' cond')
            ->leftJoin(ExchangeRates::tableName() . ' as ex', 'ex.id = cond.cond_currency')
            ->where(['cond.id' => $arCondIDs])
            ->all();

        $arCond = [];
        foreach ($arCondTmp as $cond)
            $arCond[$cond['id']] = $cond;

        $sFileName = 'payments-extend-report-' . uniqid(time()) . '.xlsx';

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator(Yii::$app->name)
            ->setLastModifiedBy(Yii::$app->user->id)
            ->setTitle(Yii::t('app/reports', 'Payments report'))
            ->setSubject(Yii::t('app/reports', 'Payments report'));

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()
            ->setCellValue('A1', 'Отчет по платежам')
            ->setCellValue('A2', 'Начальная дата:')
            ->setCellValue('B2', Yii::$app->formatter->asDate($this->dateFrom))
            ->setCellValue('A3', 'Конечная дата:')
            ->setCellValue('B3', Yii::$app->formatter->asDate($this->dateTo))
            ->setCellValue('A4', 'Общая сумма платежей:')
            ->setCellValue('B4', $data['iSumTotal'])
            ->setCellValue('A5', 'Общая прибыль:')
            ->setCellValue('B5', $data['iProfitTotal'])
            ->setCellValue('A6', 'Общий налог:')
            ->setCellValue('B6', $data['iTaxTotal'])
            ->setCellValue('A7', 'Общие производственные затраты:')
            ->setCellValue('B7', $data['iProdTotal']);


        $objPHPExcel->getActiveSheet()
            ->setCellValue('A9', 'Детализация платежей:');

        $objPHPExcel->getActiveSheet()->setCellValue('A9', Yii::t('app/reports', 'Payments ID'));
        $objPHPExcel->getActiveSheet()->setCellValue('B9', Yii::t('app/reports', 'Payments date'));
        $objPHPExcel->getActiveSheet()->setCellValue('C9', Yii::t('app/reports', 'Contractor'));
        $objPHPExcel->getActiveSheet()->setCellValue('D9', Yii::t('app/reports', 'Prospects'));

        $objPHPExcel->getActiveSheet()->setCellValue('E9', Yii::t('app/reports', 'Paid hour'));
        $objPHPExcel->getActiveSheet()->setCellValue('F9', Yii::t('app/reports', 'Spend hour'));
        $objPHPExcel->getActiveSheet()->setCellValue('G9', Yii::t('app/reports', 'Balance hour'));

        $objPHPExcel->getActiveSheet()->setCellValue('H9', Yii::t('app/reports', 'Payment owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('I9', Yii::t('app/reports', 'Sale owner'));
        $objPHPExcel->getActiveSheet()->setCellValue('J9', Yii::t('app/reports', 'Manager'));
        $objPHPExcel->getActiveSheet()->setCellValue('K9', Yii::t('app/reports', 'Legal person'));
        $objPHPExcel->getActiveSheet()->setCellValue('L9', Yii::t('app/reports', 'Банк. реквизиты'));
        $objPHPExcel->getActiveSheet()->setCellValue('M9', Yii::t('app/reports', 'Service'));
        $objPHPExcel->getActiveSheet()->setCellValue('N9', Yii::t('app/reports', 'Payment sum'));
        $objPHPExcel->getActiveSheet()->setCellValue('O9', Yii::t('app/reports', 'Payment currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('P9', Yii::t('app/reports', 'Exchange currency'));
        $objPHPExcel->getActiveSheet()->setCellValue('Q9', Yii::t('app/reports', 'Full amount BYR'));

        $objPHPExcel->getActiveSheet()->setCellValue('R9', Yii::t('app/reports', 'Profit'));
        $objPHPExcel->getActiveSheet()->setCellValue('S9', Yii::t('app/reports', 'Production'));
        $objPHPExcel->getActiveSheet()->setCellValue('T9', Yii::t('app/reports', 'Tax'));

        $objPHPExcel->getActiveSheet()->setCellValue('U9', Yii::t('app/reports', 'Corr factor'));
        $objPHPExcel->getActiveSheet()->setCellValue('V9', Yii::t('app/reports', 'Commission'));
        $objPHPExcel->getActiveSheet()->setCellValue('W9', Yii::t('app/reports', 'Sale rate'));
        $objPHPExcel->getActiveSheet()->setCellValue('X9', Yii::t('app/reports', 'Tax rate'));

        $objPHPExcel->getActiveSheet()->setCellValue('Y9', Yii::t('app/reports', 'Payment calc condition'));
        $objPHPExcel->getActiveSheet()->setCellValue('Z9', Yii::t('app/reports', 'Condition currency'));

        $objPHPExcel->getActiveSheet()->setCellValue('AA9', Yii::t('app/reports', 'Currency code'));
        $objPHPExcel->getActiveSheet()->setCellValue('AB9', Yii::t('app/reports', 'Currency name'));
        $objPHPExcel->getActiveSheet()->setCellValue('AC9', Yii::t('app/reports', 'Enroll amount'));
        $objPHPExcel->getActiveSheet()->setCellValue('AD9', Yii::t('app/reports', 'Enroll unit name'));
        $i = 10;


        foreach ($data['data'] as $key => $dt) {
            foreach ($dt as $d) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $d['id']);
                //$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, \PHPExcel_Shared_Date::PHPToExcel($d['pay_date'] + 86400));
                $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->getNumberFormat()->setFormatCode('DD.MM.YYYY');

                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, ($d['full_corp_name'] ? $d['full_corp_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, ($d['prospects_name'] ? $d['prospects_name'] : 'N/A'));

                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, isset($data['quantityHours'][$d['id']]) ? $data['quantityHours'][$d['id']]['paid'] : '');
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, isset($data['quantityHours'][$d['id']]) ? $data['quantityHours'][$d['id']]['spent'] : '');
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, isset($data['quantityHours'][$d['id']]) ? $data['quantityHours'][$d['id']]['balance'] : '');

                $objPHPExcel->getActiveSheet()->setCellValue('H' . $i, ($d['manager_name'] ? $d['manager_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $i, ($d['sale_manager_name'] ? $d['sale_manager_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $i, ($d['responsible_manager_name'] ? $d['responsible_manager_name'] : 'N/A'));

                $objPHPExcel->getActiveSheet()->setCellValue('K' . $i, ($d['legal_name'] ? $d['legal_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $i, ($d['bankName'] ? $d['bankName'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $i, ($d['service_name'] ? $d['service_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('N' . $i, $d['pay_summ']);

                $objPHPExcel->getActiveSheet()->setCellValue('O' . $i, ($d['code'] ? $d['code'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('P' . $i, isset($data['currency'][$d['id']]) ? $data['currency'][$d['id']] : '');
                $objPHPExcel->getActiveSheet()->setCellValue('Q' . $i, isset($data['fullAmount'][$d['id']]) ? $data['fullAmount'][$d['id']] : '');


                $objPHPExcel->getActiveSheet()->setCellValue('R' . $i, ($d['profit'] ? $d['profit'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('S' . $i, ($d['production'] ? $d['production'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('T' . $i, ($d['tax'] ? $d['tax'] : 'N/A'));


                $objPHPExcel->getActiveSheet()->setCellValue('U' . $i, ($d['cnd_corr_factor'] ? $d['cnd_corr_factor'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('V' . $i, ($d['cnd_commission'] ? $d['cnd_commission'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('W' . $i, ($d['cnd_sale'] ? $d['cnd_sale'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('X' . $i, ($d['cnd_tax'] ? $d['cnd_tax'] : 'N/A'));

                $objPHPExcel->getActiveSheet()->setCellValue('Y' . $i, ($d['pay_cond_name'] ? $d['pay_cond_name'] : 'N/A'));
                $objPHPExcel->getActiveSheet()->setCellValue('Z' . $i, isset($data['condCurr'][$d['id']]) ? $data['condCurr'][$d['id']] : 'N/A');

                $objPHPExcel->getActiveSheet()->setCellValue('AA' . $i, isset($arCond[$d['pay_cond_id']]) ? $arCond[$d['pay_cond_id']]['code'] : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('AB' . $i, isset($arCond[$d['pay_cond_id']]) ? $arCond[$d['pay_cond_id']]['name'] : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('AC' . $i, $d['enroll_amount'] ? $d['enroll_amount'] : 'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue('AD' . $i, $d['enroll_unit_name'] ? $d['enroll_unit_name'] : 'N/A');
                $i++;
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(Yii::getAlias('@backend/web/reports/') . $sFileName);


        return $sFileName;
    }

    /**
     * @param $data
     * @return null|string
     */
    protected function generateDocxDocument($data)
    {
        $template = \Yii::getAlias('@common/php_office_tmpl/') . 'payment_report_tpl.docx';
        $sFileName = 'payments-report-' . uniqid(time()) . '.docx';
        try {
            $doc = new \PhpOffice\PhpWord\TemplateProcessor($template);
            //название клиентской систмы
            $doc->setValue('systemName', Yii::$app->name);

            //гланая страница
            $doc->setValue('Year', date('Y'));

            //страница с общей статистикой
            $doc->setValue('startDay', Yii::$app->formatter->asDate($this->dateFrom));
            $doc->setValue('endDay', Yii::$app->formatter->asDate($this->dateTo));
            $doc->setValue('currentDay', date('Y-m-d'));
            $doc->setValue('iSumTotal', $data['iSumTotal']);
            $doc->setValue('iTaxTotal', $data['iTaxTotal']);
            $doc->setValue('iProdTotal', $data['iProdTotal']);
            $doc->setValue('iProfTotal', $data['iProfitTotal']);

            $iCount = 0;
            foreach ($data['data'] as $dt)
                if (is_array($dt))
                    $iCount += count($dt);

            //таблица Рекламная сеть Яндекса
            $doc->cloneRow('cDate', $iCount);
            $iter = 1;
            foreach ($data['data'] as $key => $dt)
                foreach ($dt as $item) {
                    $doc->setValue('cDate#' . $iter, Yii::$app->formatter->asDate($item['pay_date']));
                    $doc->setValue('contractor#' . $iter, ($item['full_corp_name'] ? $item['full_corp_name'] : 'N/A'));
                    $doc->setValue('manager#' . $iter, ($item['manager_name'] ? $item['manager_name'] : 'N/A'));
                    $doc->setValue('legalPerson#' . $iter, ($item['legal_name'] ? $item['legal_name'] : 'N/A'));
                    $doc->setValue('service#' . $iter, ($item['service_name'] ? $item['service_name'] : 'N/A'));
                    $doc->setValue('iSum#' . $iter, $item['pay_summ']);
                    $doc->setValue('currCode#' . $iter, ($item['code'] ? $item['code'] : 'N/A'));
                    $doc->setValue('exRate#' . $iter, isset($data['currency'][$item['id']]) ? Yii::$app->formatter->asDecimal($data['currency'][$item['id']]) : '');
                    $doc->setValue('iTax#' . $iter, ($item['tax'] ? Yii::$app->formatter->asDecimal($item['tax']) : 'N/A'));
                    $doc->setValue('iProd#' . $iter, ($item['production'] ? Yii::$app->formatter->asDecimal($item['production']) : 'N/A'));
                    $doc->setValue('iProfit#' . $iter, ($item['profit'] ? Yii::$app->formatter->asDecimal($item['profit']) : 'N/A'));
                    $doc->setValue('exCondRate#' . $iter, isset($data['condCurr'][$item['id']]) ? Yii::$app->formatter->asDecimal($data['condCurr'][$item['id']]) : 'N/A');
                    $iter++;
                }
            $doc->saveAs(Yii::getAlias('@backend/web/reports/') . $sFileName);
            if (file_exists(Yii::getAlias('@backend/web/reports/') . $sFileName))
                return $sFileName;

        } catch (\Exception $e) {
        }
        return NULL;
    }

    /**
     *
     */
    protected function getSales()
    {
        if (empty($this->arPaymentsInByr))
            return [];

        return $this->arSales = PaymentsSale::find()->select(['payment_id'])->where(['payment_id' => array_keys($this->arPaymentsInByr)])->column();
    }

    /**
     * @return array
     */
    protected function getSaleInfoAmount()
    {
        if (empty($this->arPaymentsInByr))
            return ['saleAmount' => 0, 'paymentWithoutSale' => 0];

        if (empty($this->arSales)) {
            return ['saleAmount' => 0, 'paymentWithoutSale' => array_sum($this->arPaymentsInByr)];
        }

        $amountSale = 0;
        $amountPayment = 0;

        foreach ($this->arPaymentsInByr as $iPayId => $payAmount) {
            if (in_array($iPayId, $this->arSales)) {
                $amountSale += (float)$payAmount;
            } else {
                $amountPayment += (float)$payAmount;
            }
        }

        return ['saleAmount' => $amountSale, 'paymentWithoutSale' => $amountPayment];
    }

} 