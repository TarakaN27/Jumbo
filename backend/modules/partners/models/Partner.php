<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.4.16
 * Time: 13.01
 */

namespace backend\modules\partners\models;


use common\models\CUser;
use common\models\ExchangeCurrencyHistory;
use common\models\PartnerCuserServ;
use common\models\AbstractActiveRecord;
use common\models\Payments;
use yii\data\ActiveDataProvider;
use Yii;

class Partner extends CUser
{
    /**
     * @return ActiveQuery
     */
    public $totalPaySum;
    public function getParnerLeads()
    {
        return $this->hasMany(PartnerCuserServ::className(),['partner_id' => 'id']);
    }

    public function searchPartners($params,$addQuery = NULL,$addParams = [])
    {
        $query = self::find();
        $query->joinWith('requisites');
        $query->where(['partner' => AbstractActiveRecord::YES]);
        $query->addSelect([CUser::tableName().'.*','totalPaySum'=>'SUM(pay_summ*rate_nbrb)']);
        $query->joinWith('parnerLeads');
        $query->joinWith('parnerLeads.partnerPayments');
        $query->joinWith('parnerLeads.partnerPayments');
        $query->leftJoin(ExchangeCurrencyHistory::tableName().' ON '.ExchangeCurrencyHistory::tableName().'.currency_id='.Payments::tableName().'.currency_id AND '.ExchangeCurrencyHistory::tableName().'.date = DATE_FORMAT(FROM_UNIXTIME('.Payments::tableName().'.pay_date), \'%Y-%m-%e\')');
        $query->groupBy([static::tableName().'.id']);

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
                   'created_at'=>SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            CUser::tableName().'.id' => $this->id,
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
}