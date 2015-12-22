<?php

namespace common\models\search;

use common\models\CrmTaskAccomplices;
use common\models\CrmTaskWatcher;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CrmTask;
use yii\db\ActiveQuery;

/**
 * CrmTaskSearch represents the model behind the search form about `common\models\CrmTask`.
 */
class CrmTaskSearch extends CrmTask
{

    CONST
        VIEW_TYPE_ALL = 'all',          //все
        VIEW_TYPE_ASSIGN = 'assign',    //делаю
        VIEW_TYPE_ASSIST = 'assist',    //помогаю
        VIEW_TYPE_CREATE = 'create',    //созданные
        VIEW_TYPE_WATCH = 'watch';      //наблюдаю

    /**
     * @return array
     */
    public static function getViewTypeArr()
    {
        return [
            self::VIEW_TYPE_ALL => Yii::t('app/crm','View type all'),
            self::VIEW_TYPE_ASSIGN => Yii::t('app/crm','View type assign'),
            self::VIEW_TYPE_ASSIST => Yii::t('app/crm','View type assist'),
            self::VIEW_TYPE_CREATE => Yii::t('app/crm','View type create'),
            self::VIEW_TYPE_WATCH => Yii::t('app/crm','View type watch')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'priority', 'type', 'task_control', 'parent_id', 'assigned_id', 'created_by', 'time_estimate', 'status', 'date_start', 'duration_fact', 'closed_by', 'closed_date', 'cmp_id', 'contact_id', 'dialog_id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'description', 'deadline'], 'safe'],
        ];
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
    public function search($params,$viewType = self::VIEW_TYPE_ALL,$addQuery = NULL,$addParams = [])
    {
        $query = CrmTask::find();
        $query = $this->getAdditionQuery($query,$viewType);
        if(!is_null($addQuery)) //дополнительное условие
            $query->andWhere($addQuery,$addParams);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => Yii::$app->params['defaultPageSize'],
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'deadline' => $this->deadline,
            'priority' => $this->priority,
            'type' => $this->type,
            'task_control' => $this->task_control,
            'parent_id' => $this->parent_id,
            'assigned_id' => $this->assigned_id,
            'created_by' => $this->created_by,
            'time_estimate' => $this->time_estimate,
            'status' => $this->status,
            'date_start' => $this->date_start,
            'duration_fact' => $this->duration_fact,
            'closed_by' => $this->closed_by,
            'closed_date' => $this->closed_date,
            'cmp_id' => $this->cmp_id,
            'contact_id' => $this->contact_id,
            'dialog_id' => $this->dialog_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }

    protected function getAdditionQuery(ActiveQuery $query,$viewType)
    {
        $iUserID = Yii::$app->user->id;    //ID пользователя

        switch ($viewType)
        {
            case self::VIEW_TYPE_ALL:       //все таски
                $query
                    ->joinWith('crmTaskAccomplices')                                        //таблица помогаю
                    ->joinWith('crmTaskWatchers')                                           //таблица наблюдаю
                    ->where(['created_by' => $iUserID])                                     //все созданные
                    ->orWhere(['assigned_id' => $iUserID])                                  //все за которые отвественный
                    ->orWhere([CrmTaskAccomplices::tableName().'.buser_id' => $iUserID])    //все которым помогаю
                    ->orWhere([CrmTaskWatcher::tableName().'.buser_id' => $iUserID]);       //все за которыми смотрю
                break;

            case self::VIEW_TYPE_ASSIGN:    //делаю
                $query->where(['assigned_id' => $iUserID]);
                break;

            case self::VIEW_TYPE_CREATE:    //создал
                $query->where(['created_by' => $iUserID]);
                break;

            case self::VIEW_TYPE_ASSIST:    //помогаю
                $query->joinWith('crmTaskAccomplices')->where([CrmTaskAccomplices::tableName().'.buser_id' => $iUserID]);
                break;

            case self::VIEW_TYPE_WATCH:     //наблюдаю
                $query->joinWith('crmTaskWatchers')->orWhere([CrmTaskWatcher::tableName().'.buser_id' => $iUserID]);
                break;

            default:
                break;
        }

        return $query;
    }
}
