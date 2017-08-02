<?php

namespace common\models\search;

use common\models\AbstractActiveRecord;
use common\models\CuserQuantityHour;
use common\models\CUserRequisites;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CUser;
use yii\helpers\ArrayHelper;

/**
 * CUserSearch represents the model behind the search form about `common\models\CUser`.
 */
class CUserSearch extends CUser
{
    public
        $quantityHour,
        $corp_name,
        $c_email,
        $ext_email,
        $phone,
        $fio;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[

                'id', 'ext_id', 'type', 'manager_id','prospects_id',
                'role', 'status', 'created_at', 'updated_at','contractor','manager_crc_id','source_id','partner_manager_id'
            ], 'integer'],
            [[
                'phone','c_email','ext_email','fio','username',
                'auth_key', 'password_hash', 'password_reset_token',
                'email','corp_name'
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
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$addQuery = NULL,$addParams = [])
    {
        $query = CUser::find()->with('manager','userType','requisites','quantityHour','managerCrc','source');
        $query->joinWith('requisites');
        $query->joinWith('quantityHour');

        if(!is_null($addQuery))
            $query->where($addQuery,$addParams);

        if(!Yii::$app->user->can('adminRights'))    //показываем архивные компании только админам
            $query->notArchive();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => [
                    'updated_at'=>SORT_DESC
                ]
            ]
        ]);

        // сортировка по присоедененной таблице CuserQuantityHour
        $dataProvider->sort->attributes['quantityHour'] = [
            'asc'=>['ISNULL('.CuserQuantityHour::tableName().'.hours)' => SORT_ASC,'('.CuserQuantityHour::tableName().'.hours - '.CuserQuantityHour::tableName().'.spent_time)'=>SORT_ASC],
            'desc'=>['ISNULL('.CuserQuantityHour::tableName().'.hours)' => SORT_ASC,'('.CuserQuantityHour::tableName().'.hours - '.CuserQuantityHour::tableName().'.spent_time)'=>SORT_DESC],
        ];

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
        $query->andFilterWhere(['like',CUserRequisites::tableName().'.ext_email',$this->ext_email]);

        return $dataProvider;
    }
}
