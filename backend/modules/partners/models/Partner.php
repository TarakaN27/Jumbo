<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.4.16
 * Time: 13.01
 */

namespace backend\modules\partners\models;


use common\models\CUser;

use common\models\PartnerCuserServ;
use common\models\AbstractActiveRecord;

use yii\data\ActiveDataProvider;
use Yii;
use yii\helpers\ArrayHelper;
use common\models\CUserRequisites;
use common\models\search\CUserSearch;
class Partner extends CUserSearch
{
    /**
     * @return ActiveQuery
     */
    public $totalCurrentMonthSum;
    public $totalProcessedSum;
    public $totalPercentSum;
    public $availToWithdrawal;
    public $beginDate;
    public $endDate;
    public $showByAllPayments;
    public function rules()
    {
        return [
            [[

                'id', 'ext_id', 'type', 'manager_id','prospects_id',
                'role', 'status', 'created_at', 'updated_at','contractor','manager_crc_id','source_id','partner_manager_id'
            ], 'integer'],
            [[
                'phone','c_email','fio','username',
                'auth_key', 'password_hash', 'password_reset_token',
                'email','corp_name', 'totalCurrentMonthSum', 'totalProcessedSum','totalPercentSum','availToWithdrawal',
                'beginDate', 'endDate', 'showByAllPayments'
            ], 'safe'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $arPLabel = parent::attributeLabels();
        return ArrayHelper::merge($arPLabel,[
            'fio' => Yii::t('app/users', 'FIO'),
            'corp_name' => Yii::t('app/users', 'Corp Name'),
            'phone' => Yii::t('app/users', 'Phone'),
            'quantityHour' => Yii::t('app/users','Quantity hours'),
            'totalCurrentMonthSum'=> Yii::t('app/users','Total Current Month Sum'),
            'totalProcessedSum'=>Yii::t('app/users','Total Processed Sum'),
            'totalPercentSum'=>Yii::t('app/users','Total Percent Sum'),
            'availToWithdrawal'=>Yii::t('app/users','Avail To Withdrawal'),
            'beginDate' => Yii::t('app/users','Begin date'),
            'endDate' => Yii::t('app/users','End date'),
            'showByAllPayments' => Yii::t('app/users','Show By All Payments'),
        ]);
    }
    public function getParnerLeads()
    {
        return $this->hasMany(PartnerCuserServ::className(),['partner_id' => 'id']);
    }
    public function getSelectedDate(){
        $selectedDateWhere = '';
        if($this->beginDate){
            $selectedDateWhere.=' AND p.pay_date>='.strtotime($this->beginDate);
        }
        if($this->endDate){
            $selectedDateWhere.=' AND p.pay_date<='.strtotime($this->endDate);
        }
        return $selectedDateWhere;
    }
    public function getTotalSum($query){
        $tempQuery = clone $query;
        $tempQuery->select = [
            'totalCurrentMonthSum'=>'SUM(total_lead_sum)',
            'availToWithdrawal'=>'SUM(purse.amount- IFNULL(avail.pending_sum,0))',
            'totalProcessedSum'=>'SUM(total_processed_sum)',
            'totalPercentSum' => 'SUM(total_percent_sum)'
        ];
        return $tempQuery->one();
    }
    public function searchPartners($params,$addQuery = NULL,$addParams = [])
    {
        $this->load($params);
        $query = self::find()->alias('u');
        $query->joinWith('requisites');
        $currentMonthStart = strtotime(date("Y-m")."-01");
        $query->joinWith('partnerPurse purse');
        $query->select(['u.*', 'totalCurrentMonthSum'=>'total_lead_sum', 'availToWithdrawal'=>'purse.amount- IFNULL(avail.pending_sum,0)','totalProcessedSum'=>'total_processed_sum', 'totalPercentSum'=>'total_percent_sum']);
        $query->where(['partner' => AbstractActiveRecord::YES]);
        $selectedDateWhere = $this->getSelectedDate();
        if($this->showByAllPayments == 1) {

            $query->leftJoin(['p' => "(select partner_id, SUM(p.pay_summ*c.rate_nbrb) as total_lead_sum FROM wm_payments p LEFT JOIN wm_exchange_currency_history c ON p.currency_id = c.currency_id and c.date = DATE_FORMAT(FROM_UNIXTIME(p.pay_date), '%Y-%m-%e') LEFT JOIN (select distinct cuser_id, partner_id, connect from wm_partner_cuser_serv as partner) l ON l.cuser_id= p.cuser_id where l.connect<=FROM_UNIXTIME(pay_date) and p.pay_date >= $currentMonthStart  GROUP BY partner_id)"], 'p.partner_id=u.id');
            $query->leftJoin(['pp'=>"((select partner_id, SUM(p.pay_summ*c.rate_nbrb) as total_processed_sum FROM wm_payments p LEFT JOIN wm_exchange_currency_history c ON p.currency_id = c.currency_id and c.date = DATE_FORMAT(FROM_UNIXTIME(p.pay_date), '%Y-%m-%e') LEFT JOIN (select distinct cuser_id, partner_id, connect from wm_partner_cuser_serv as partner) l ON l.cuser_id= p.cuser_id where l.connect<=FROM_UNIXTIME(pay_date) $selectedDateWhere and p.pay_date<$currentMonthStart GROUP BY partner_id))"],'pp.partner_id=u.id');
            $query->leftJoin(['percent'=>"(select h.cuser_id, SUM(h.amount) as total_percent_sum  from wm_partner_purse_history h LEFT JOIN wm_payments p ON p.id = h.payment_id LEFT JOIN wm_exchange_currency_history c ON p.currency_id = c.currency_id and c.date = DATE_FORMAT(FROM_UNIXTIME(p.pay_date), '%Y-%m-%e') where type=5 $selectedDateWhere GROUP BY cuser_id)"],'percent.cuser_id=u.id');
        }else {
            $query->leftJoin(['p' => "(select partner_id, SUM(p.pay_summ*c.rate_nbrb) as total_lead_sum from wm_partner_cuser_serv l LEFT JOIN wm_payments p ON l.cuser_id = p.cuser_id AND l.service_id=p.service_id LEFT JOIN wm_exchange_currency_history c ON p.currency_id = c.currency_id and c.date = DATE_FORMAT(FROM_UNIXTIME(p.pay_date), '%Y-%m-%e') where connect<=FROM_UNIXTIME(pay_date) and p.pay_date >= $currentMonthStart  GROUP BY partner_id)"], 'p.partner_id=u.id');
            $query->leftJoin(['pp'=>"(select h.cuser_id, SUM(p.pay_summ*c.rate_nbrb) as total_processed_sum, SUM(h.amount) as total_percent_sum  from wm_partner_purse_history h LEFT JOIN wm_payments p ON p.id = h.payment_id LEFT JOIN wm_exchange_currency_history c ON p.currency_id = c.currency_id and c.date = DATE_FORMAT(FROM_UNIXTIME(p.pay_date), '%Y-%m-%e') where type=5 $selectedDateWhere GROUP BY cuser_id)"],'pp.cuser_id=u.id');
        }


        $query->leftJoin(['avail'=>"(select partner_id, SUM(pending_in_base_currency) as pending_sum from wm_partner_withdrawal_request  where status=5 GROUP BY partner_id)"],'avail.partner_id=u.id');

        if(!empty($addQuery))
            $query->andWhere($addQuery);

        if(!empty($addParams))
            $query->params($addParams);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => [
                   'totalCurrentMonthSum'=>SORT_DESC
                ],
                'attributes'=>[
                    'totalCurrentMonthSum',
                    'totalProcessedSum',
                    'totalPercentSum',
                    'availToWithdrawal'
                ]
            ]
        ]);
        if (!$this->load($params) || !$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        list($this->totalCurrentMonthSum, $this->totalProcessedSum, $this->totalPercentSum, $this->availToWithdrawal) = str_replace(',','.',[$this->totalCurrentMonthSum, $this->totalProcessedSum, $this->totalPercentSum, $this->availToWithdrawal]);

        $query->andFilterWhere([
            'u.id' => $this->id,
            CUser::tableName().'.ext_id' => $this->ext_id,
            'type' => $this->type,
            'manager_id' => $this->manager_id,
            'manager_crc_id' => $this->manager_crc_id,
            'role' => $this->role,
            'status' => $this->status,
            'contractor' => $this->contractor,
            'prospects_id' => $this->prospects_id,
            'source_id' => $this->source_id,
            'partner_manager_id' => $this->partner_manager_id,
            'ROUND(p.total_lead_sum,2)' =>$this->totalCurrentMonthSum,
            'ROUND(pp.total_processed_sum,2)' => $this->totalProcessedSum,
            'ROUND(pp.total_percent_sum,2)' => $this->totalPercentSum,
            'ROUND(purse.amount - IFNULL(avail.pending_sum,0),2)' => $this->availToWithdrawal,
            CUser::tableName().'created_at' => $this->created_at,
            CUser::tableName().'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email]);
        if(!empty($this->fio))
        {
            $query->andWhere(' ( '.CUserRequisites::tableName().'.j_lname LIKE :fio OR '.
                CUserRequisites::tableName().'.j_fname LIKE :fio OR '.
                CUserRequisites::tableName().'.j_mname LIKE :fio ) ',[':fio' => '%'.$this->fio.'%' ]);
        }

        if(!empty($this->corp_name))
        {
            $query->andWhere('( '.
                CUserRequisites::tableName().'.site LIKE :corp_name OR '.
                CUserRequisites::tableName().'.corp_name LIKE :corp_name OR '.
                CUserRequisites::tableName().'.j_lname LIKE :corp_name OR '.
                CUserRequisites::tableName().'.j_fname LIKE :corp_name OR '.
                CUserRequisites::tableName().'.j_mname LIKE :corp_name)',[':corp_name' => '%'.$this->corp_name.'%' ]);
        }
        $query->andFilterWhere(['like',CUserRequisites::tableName().'.c_phone',$this->phone]);
        $query->andFilterWhere(['like',CUserRequisites::tableName().'.c_email',$this->c_email]);

        return $dataProvider;
    }
/*    public function afterFind()
    {
        parent::afterFind(); // TODO: Change the autogenerated stub
        if($this->partnerScheme->counting_base == PartnerSchemes::COUNTING_BASE_ENROLL){
            if($this->totalPaySum>0){
                $excludeSum = Yii::$app->db->createCommand(
                        "select SUM(p.pay_summ*c.rate_nbrb) as total_lead_sum 
                        from {{%partner_cuser_serv}} l 
                        LEFT JOIN wm_payments p ON l.cuser_id = p.cuser_id AND l.service_id=p.service_id
                        LEFT JOIN wm_services s ON s.id = p.service_id
                        LEFT JOIN wm_enrollment_request r ON r.payment_id = p.id
                        LEFT JOIN wm_exchange_currency_history c ON p.currency_id = c.currency_id and c.date = DATE_FORMAT(FROM_UNIXTIME(p.pay_date), '%Y-%m-%e') 
                        where partner_id = :partnerId and connect<=FROM_UNIXTIME(p.pay_date) and r.payment_id is null and s.allow_enrollment=1"
                    ,['partnerId'=>$this->id])->queryOne();
                $this->totalPaySum -= $excludeSum['total_lead_sum'];
            }
        }
    }
*/
}