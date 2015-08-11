<?php

namespace common\models\search;

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
        $corp_name,
        $fio;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ext_id', 'type', 'manager_id', 'role', 'status', 'created_at', 'updated_at'], 'integer'],
            [['fio','username', 'auth_key', 'password_hash', 'password_reset_token', 'email','corp_name'], 'safe'],
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
    public function search($params)
    {
        $query = CUser::find()->with('manager','userType','requisites');
        $query->joinWith('requisites');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,200]
            ],
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
            'role' => $this->role,
            'status' => $this->status,
            CUser::tableName().'created_at' => $this->created_at,
            CUser::tableName().'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email]);
        if(!empty($this->fio))
            $query->andWhere(' ( '.CUserRequisites::tableName().'.j_lname LIKE "'.$this->fio.'%" OR '.
                CUserRequisites::tableName().'.j_fname LIKE "'.$this->fio.'%" OR '.
                CUserRequisites::tableName().'.j_mname LIKE "'.$this->fio.'%" ) ');

        if(!empty($this->corp_name))
            $query->andWhere('( '.
                CUserRequisites::tableName().'.corp_name LIKE "%'.$this->corp_name.'%" OR '.
                CUserRequisites::tableName().'.j_lname LIKE "%'.$this->corp_name.'%" OR '.
                CUserRequisites::tableName().'.j_fname LIKE "%'.$this->corp_name.'%" OR '.
                CUserRequisites::tableName().'.j_mname LIKE "%'.$this->corp_name.'%")');

        //$query->andFilterWhere(['like',CUserRequisites::tableName().'.j_lname',$this->fio]);
        return $dataProvider;
    }
}
