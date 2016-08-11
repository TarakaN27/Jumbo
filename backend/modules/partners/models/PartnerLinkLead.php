<?php
/**
 * Created by PhpStorm.
 * User: a.ermalovich
 * Date: 11.08.2016
 * Time: 15:43
 */

namespace backend\modules\partners\models;


use common\models\PartnerCuserServ;
use yii\data\ActiveDataProvider;
use common\models\CUserRequisites;

class PartnerLinkLead extends PartnerCuserServ
{
    public function rules()
    {
        return [
            [['cuser_id','id','service_id','connect', 'archive'],'safe']
        ];
    }
    public function searchLinkLead($params,$pid)
    {
        $query = self::find()->alias('l')->where(['partner_id' => $pid]);
        $query->joinWith('cuser.requisites');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => -1,
            ],
        ]);

        if (!$this->load($params) || !$this->validate()) {

            return $dataProvider;
        }

        if(!empty($this->cuser_id))
        {
            $query->andWhere('( '.
                CUserRequisites::tableName().'.site LIKE :corp_name OR '.
                CUserRequisites::tableName().'.corp_name LIKE :corp_name OR '.
                CUserRequisites::tableName().'.j_lname LIKE :corp_name OR '.
                CUserRequisites::tableName().'.j_fname LIKE :corp_name OR '.
                CUserRequisites::tableName().'.j_mname LIKE :corp_name)',[':corp_name' => '%'.$this->cuser_id.'%' ]);
        }
        $query->andFilterWhere([
            'l.id' => $this->id,
            'service_id'=>$this->service_id,
            'l.archive' => $this->archive,

        ]);
        if($this->connect) {
            $query->andFilterWhere([
                'connect'=>date("Y-m-d", strtotime($this->connect)),
            ]);
        }

        return $dataProvider;
    }
}