<?php

namespace common\models\search;

use common\models\CrmTaskAccomplices;
use common\models\CrmTaskWatcher;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CrmTask;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * CrmTaskSearch represents the model behind the search form about `common\models\CrmTask`.
 */
class CrmTaskSearch extends CrmTask
{
    CONST
        VIEW_TYPE_FULL_TASK_AND_OWNER = 'full_task_with_owner',
        VIEW_TYPE_FULL_TASK = 'full_task', //все задачи
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
        $arType = [
            self::VIEW_TYPE_ALL => Yii::t('app/crm','View type all'),
            self::VIEW_TYPE_ASSIGN => Yii::t('app/crm','View type assign'),
            self::VIEW_TYPE_ASSIST => Yii::t('app/crm','View type assist'),
            self::VIEW_TYPE_CREATE => Yii::t('app/crm','View type create'),
            self::VIEW_TYPE_WATCH => Yii::t('app/crm','View type watch')
        ];

        if(Yii::$app->user->can('adminRights'))
            $arType[self::VIEW_TYPE_FULL_TASK] = Yii::t('app/crm','View type users task');

        return $arType;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'priority', 'type', 'task_control', 'parent_id', 'assigned_id', 'created_by', 'time_estimate', 'date_start', 'duration_fact', 'closed_by', 'closed_date', 'cmp_id', 'contact_id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'description','status',  'deadline'], 'safe'],
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
    public function search($params,$viewType = self::VIEW_TYPE_ALL,$addQuery = NULL,$addParams = [],$cachePageSize = FALSE)
    {
        $query = CrmTask::find()->with('cmp','cmp.requisites');
        $query = $this->getAdditionQuery($query,$viewType);
        if(!is_null($addQuery)) //дополнительное условие
            $query->andWhere($addQuery,$addParams);

        $defaultPageSize = Yii::$app->params['defaultPageSize'];

        if($cachePageSize)
        {
            $key_per_page = 'task_per_page_'.Yii::$app->user->id;
            if(!isset($params['per-page']))
            {
                $tmp = Yii::$app->session->get($key_per_page);
                if(!empty($tmp)) {
                    $params['per-page'] = $tmp;
                    $_GET['per-page'] = $tmp;
                }
            }else{
                Yii::$app->session->set($key_per_page,$params['per-page']);
                if(!isset($_GET['per-page']))
                    $_GET['per-page'] = $params['per-page'];
            }

            if(isset($params['per-page']))
                $defaultPageSize = $params['per-page'];
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $defaultPageSize,
                'pageSizeLimit' => [1,1000]
            ],
            'sort'=> [
                'defaultOrder' => ['updated_at'=>SORT_DESC]
            ]
        ]);



        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        if(!empty($this->deadline))
            $query->andWhere("DATE_FORMAT(deadline, '%Y-%m-%d') = :deadline",['deadline' => $this->deadline]);

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

            case self::VIEW_TYPE_FULL_TASK:
                if(!Yii::$app->user->can('adminRights'))
                {
                    $query->where('1=0');
                }
            break;

            case self::VIEW_TYPE_FULL_TASK_AND_OWNER:
                if(!Yii::$app->user->can('adminRights'))
                {
                    $query
                        ->joinWith('crmTaskAccomplices')                                        //таблица помогаю
                        ->joinWith('crmTaskWatchers')                                           //таблица наблюдаю
                        ->where(['created_by' => $iUserID])                                     //все созданные
                        ->orWhere(['assigned_id' => $iUserID])                                  //все за которые отвественный
                        ->orWhere([CrmTaskAccomplices::tableName().'.buser_id' => $iUserID])    //все которым помогаю
                        ->orWhere([CrmTaskWatcher::tableName().'.buser_id' => $iUserID]);       //все за которыми смотрю
                }
                break;
            default:
                $query->where('1=0');
                break;
        }

        return $query;
    }
}
