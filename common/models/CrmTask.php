<?php

namespace common\models;

use common\components\behavior\history\ModelHistoryBehavior;
use common\components\behavior\notifications\TaskNotificationBehavior;
use common\components\behavior\Task\TaskActionBehavior;
use common\components\helpers\CustomHelper;
use common\components\managers\DialogManager;
use common\components\notification\RedisNotification;
use Yii;
use backend\models\BUser;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%crm_task}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $deadline
 * @property integer $priority
 * @property integer $type
 * @property integer $task_control
 * @property integer $parent_id
 * @property integer $assigned_id
 * @property integer $created_by
 * @property integer $time_estimate
 * @property integer $status
 * @property integer $date_start
 * @property integer $duration_fact
 * @property integer $closed_by
 * @property integer $closed_date
 * @property integer $cmp_id
 * @property integer $contact_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $payment_request
 * @property integer $repeat_task
 * @property integer $recurring_id
 * @property integer $recurring_last_upd

 *
 * @property Dialogs $dialog
 * @property BUser $assigned
 * @property BUser $closedBy
 * @property CUser $cmp
 * @property CrmCmpContacts $contact
 * @property BUser $createdBy
 * @property CrmTask $parent
 * @property CrmTask[] $crmTasks
 * @property CrmTaskAccomplices[] $crmTaskAccomplices
 * @property BUser[] $busers
 * @property CrmTaskLogTime[] $crmTaskLogTimes
 * @property CrmTaskWatcher[] $crmTaskWatchers
 * @property BUser[] $busers0
 */
class CrmTask extends AbstractActiveRecord
{
    //статусы задачи
    CONST
        STATUS_OPENED = 1,
        STATUS_IN_PROGRESS = 2,
        STATUS_NEED_ACCEPT = 3,
        STATUS_CLOSE  = 4;

    //приоритет задач
    CONST
        PRIORITY_HIGH = 3,
        PRIORITY_MIDDLE = 2,
        PRIORITY_LOW = 1;

    //тип задачи
    CONST
        TYPE_TASK = 1,
        TYPE_MEETING = 2,
        TYPE_CALL = 3,
        TYPE_OTHER =4;

    CONST
        EVENT_UPDATE_DIALOG = 'upd_dialog';

    public
        $arrWatch = [],
        $arrAcc = [],
        $arrFiles = [],
        $hourEstimate = '',
        $minutesEstimate = '';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_task}}';
    }

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_TASK => Yii::t('app/crm','Task'),
            self::TYPE_CALL => Yii::t('app/crm','Call'),
            self::TYPE_MEETING => Yii::t('app/crm','Meeting'),
            self::TYPE_OTHER => Yii::t('app/crm','Other')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeArr();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::STATUS_OPENED => Yii::t('app/crm','Status open'),
            self::STATUS_IN_PROGRESS => Yii::t('app/crm','Status in progress'),
            self::STATUS_NEED_ACCEPT => Yii::t('app/crm','Status done'),
            self::STATUS_CLOSE => Yii::t('app/crm','Status close')
        ];
    }

    /**
     * @return array
     */
    public static function getStatusColorClassArr()
    {
        return [
            self::STATUS_OPENED => 'open_class',
            self::STATUS_IN_PROGRESS => 'in_progress_class',
            self::STATUS_NEED_ACCEPT => 'done_class',
            self::STATUS_CLOSE => Yii::t('app/crm','close_class')
        ];
    }

    /**
     * @return bool
     */
    public function getStatusColorClass()
    {
        $tmp = self::getStatusColorClassArr();
        return isset($tmp[$this->status]) ? $tmp[$this->status] : '';
    }

    /**
     * @return string
     */
    public function getStatusStr()
    {
        $tmp = self::getStatusArr();
        return isset($tmp[$this->status]) ? $tmp[$this->status] : 'N/A';
    }

    /**
     * @return array
     */
    public static function getPriorityArr()
    {
        return [
            self::PRIORITY_HIGH => Yii::t('app/crm','Priority high'),
            self::PRIORITY_MIDDLE => Yii::t('app/crm','Priority middle'),
            self::PRIORITY_LOW => Yii::t('app/crm','Priority low')
        ];
    }

    /**
     * @return string
     */
    public function getPriorityStr()
    {
        $tmp = self::getPriorityArr();
        return isset($tmp[$this->priority]) ? $tmp[$this->priority] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'assigned_id', 'created_by','priority','cmp_id'], 'required','except' => 'changeStatus'],
            [['description'], 'string'],
            [['deadline','arrFiles'], 'safe'],
            [[
                'priority', 'type', 'task_control',
                'parent_id', 'assigned_id', 'created_by',
                'time_estimate', 'status', 'date_start',
                'duration_fact', 'closed_by', 'closed_date',
                'cmp_id', 'contact_id',
                'created_at', 'updated_at','hourEstimate',
                'minutesEstimate','payment_request','repeat_task',
                'recurring_id','recurring_last_upd'
            ], 'integer'],
            ['minutesEstimate','integer','min' => 0,'max' => 59],

            [['title'], 'string', 'max' => 255],
            ['status','default','value'=>self::STATUS_OPENED],
            [['arrAcc','arrWatch'], 'each', 'rule' => ['integer']],
            //[['arrFiles'], 'file', 'skipOnEmpty' => false],
            ['parent_id','validateParent'],
            ['repeat_task','default','value' => self::NO]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'title' => Yii::t('app/crm', 'Title'),
            'description' => Yii::t('app/crm', 'Description'),
            'deadline' => Yii::t('app/crm', 'Deadline'),
            'priority' => Yii::t('app/crm', 'Priority'),
            'type' => Yii::t('app/crm', 'Type'),
            'task_control' => Yii::t('app/crm', 'Task Control'),
            'parent_id' => Yii::t('app/crm', 'Parent ID'),
            'assigned_id' => Yii::t('app/crm', 'Assigned ID'),
            'created_by' => Yii::t('app/crm', 'Created By'),
            'time_estimate' => Yii::t('app/crm', 'Time Estimate'),
            'status' => Yii::t('app/crm', 'Status'),
            'date_start' => Yii::t('app/crm', 'Date Start'),
            'duration_fact' => Yii::t('app/crm', 'Duration Fact'),
            'closed_by' => Yii::t('app/crm', 'Closed By'),
            'closed_date' => Yii::t('app/crm', 'Closed Date'),
            'cmp_id' => Yii::t('app/crm', 'Cmp ID'),
            'contact_id' => Yii::t('app/crm', 'Contact ID'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
            'hourEstimate' => Yii::t('app/crm', 'Hour'),
            'minutesEstimate' => Yii::t('app/crm', 'Minutes'),
            'arrAcc' =>  Yii::t('app/crm', 'Accomplices'),
            'arrFiles' => Yii::t('app/crm', 'arrFiles'),
            'payment_request' => Yii::t('app/crm', 'Payment request'),
            'arrWatch' => Yii::t('app/crm','Watchers'),
            'repeat_task' => Yii::t('app/crm','Repeat task'),
            'recurring_id' => Yii::t('app/crm','Recurring id'),
            'recurring_last_upd' => Yii::t('app/crm','Recurring last update'),
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateParent($attribute,$params)
    {
        if(!empty($this->parent_id))
        {
            if(CrmTask::find()->where(['id' => $this->parent_id])->andWhere('parent_id is not null or parent_id != 0')->exists())
            {
                $this->addError($attribute,Yii::t('app/crm','This task can not be parent'));
            }
        }
        if(!$this->isNewRecord && !empty($this->parent_id))
        {
            if(CrmTask::find()->where(['parent_id' => $this->id])->exists())
            {
                $this->addError($attribute,Yii::t('app/crm','For this task can not set parent'));
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDialog()
    {
        return $this->hasOne(Dialogs::className(), ['crm_task_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssigned()
    {
        return $this->hasOne(BUser::className(), ['id' => 'assigned_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClosedBy()
    {
        return $this->hasOne(BUser::className(), ['id' => 'closed_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmp()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cmp_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(CrmCmpContacts::className(), ['id' => 'contact_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(BUser::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(CrmTask::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTasks()
    {
        return $this->hasMany(CrmTask::className(), ['parent_id' => 'id']);
    }

    /**
     * Получаем связь задача пользователь. Можем получить IDs пользователй, которые помогают
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTaskAccomplices()
    {
        return $this->hasMany(CrmTaskAccomplices::className(), ['task_id' => 'id']);
    }

    /**
     * Получаем пользователей(объекты),которые помогают
     * @return \yii\db\ActiveQuery
     */
    public function getBusersAccomplices()
    {
        return $this->hasMany(BUser::className(), ['id' => 'buser_id'])->viaTable('{{%crm_task_accomplices}}', ['task_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTaskLogTimes()
    {
        return $this->hasMany(CrmTaskLogTime::className(), ['task_id' => 'id']);
    }

    /**
     * Получаем связь задача пользователь. Можем получить IDS пользователей
     * @return \yii\db\ActiveQuery
     */
    public function getCrmTaskWatchers()
    {
        return $this->hasMany(CrmTaskWatcher::className(), ['task_id' => 'id']);
    }

    /**
     * Получаем пользователей(объекты) которые наблюдают
     * @return \yii\db\ActiveQuery
     */
    public function getBusersWatchers()
    {
        return $this->hasMany(BUser::className(), ['id' => 'buser_id'])->viaTable('{{%crm_task_watcher}}', ['task_id' => 'id']);
    }

    /**
     * Файлы задач
     * @return \yii\db\ActiveQuery
     */
    public function getTaskFiles()
    {
        return $this->hasMany(CrmCmpFile::className(),['task_id' => 'id'])->orderBy([CrmCmpFile::tableName().'.updated_at' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayRequest()
    {
        return $this->hasOne(PaymentRequest::className(),['id' => 'payment_request']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildTask()
    {
        return $this->hasMany(self::className(),['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRepeatTask()
    {
        return $this->hasOne(CrmTaskRepeat::className(),['task_id' => 'id']);
    }

    /**
     *
     */
    public function afterFind()
    {
        if(!empty($this->time_estimate))
        {
            $this->hourEstimate = sprintf('%02d', $this->time_estimate/3600);
            $this->minutesEstimate = sprintf('%02d', ($this->time_estimate % 3600)/60);
        }

        return parent::afterFind();
    }
    /**
     *
     */
    public function beforeSave($insert)
    {
        if(!empty($this->hourEstimate) || !empty($this->minutesEstimate))
            $this->time_estimate = (int)$this->minutesEstimate*60 + (int)$this->hourEstimate*3600;

        if(!empty($this->deadline))
        {
            $this->deadline = date('Y-m-d H:i',strtotime($this->deadline));
        }

        return parent::beforeSave($insert);
    }

    /**
     * Поведения
     * @return array
     */
    public function behaviors()
    {
        $arParent = parent::behaviors();
        return ArrayHelper::merge($arParent,[
            TaskNotificationBehavior::className(),      //уведомления
            TaskActionBehavior::className(),            //поведение действий
            [
                'class' => ModelHistoryBehavior::className(),   //история изменений модели
                'changedFields' => [
                    'title' => false,
                    'description' => false,
                    'deadline' => false,
                    'priority' => 'getPriorityStr',
                    'type' => 'getTypeStr',
                    'time_estimate' => 'getFormatedTimeEstimate',
                    'status' => 'getStatusStr'
                ]
            ]
        ]);
    }

    /**
     * @return string
     */
    public function getFormatedTimeEstimate()
    {
        return CustomHelper::getFormatedTaskTime($this->time_estimate);
    }

    /**
     * Изменение стутуса задачи.
     * Контроль правильности перехода статусов
     * @param $iStatus
     * @return int|null
     */
    public function changeTaskStatus($iStatus)
    {
        $rtnStatus = NULL;
        $tmp = self::getStatusArr();
        if(!in_array($iStatus,array_keys($tmp)))
            throw new InvalidParamException('Invalid status');
        $this->setScenario('changeStatus');
        switch($iStatus)
        {
            case self::STATUS_IN_PROGRESS: //статус "в процессе" можно перейти из статуса "открыт"
                if($this->status == self::STATUS_OPENED)
                {
                    $this->status = self::STATUS_IN_PROGRESS;
                    if($this->save())
                        $rtnStatus = $this->status;
                }
                break;

            case self::STATUS_OPENED: //статус "открыт" можно перейти из статуса "Закрыт" и "В процессе" или "требуется подтверждение"
                if($this->status == self::STATUS_IN_PROGRESS || $this->status == self::STATUS_CLOSE || $this->status == self::STATUS_NEED_ACCEPT)
                {
                    $this->status = self::STATUS_OPENED;
                    if($this->save())
                        $rtnStatus = $this->status;
                }
                break;

            case self::STATUS_CLOSE: //статус "закрыт" можно перейти из статусов "в процессе" или "требуется контроль"
                //из статуса "в процессе" можно перейти в статус "закрыт", если не требуется "контроля выполнения"
                if(
                    ($this->status == self::STATUS_IN_PROGRESS && $this->task_control != 1)
                    ||
                    ($this->status == self::STATUS_IN_PROGRESS && $this->created_by == Yii::$app->user->id)
                )
                {
                    $this->status = self::STATUS_CLOSE;
                    if($this->save())
                        $rtnStatus = $this->status;
                }
                //из статуса "Контроль выполнения" в статус "Закрыт"
                if($this->status == self::STATUS_NEED_ACCEPT)
                {
                    $this->status = self::STATUS_CLOSE;

                    if($this->save())
                        $rtnStatus = $this->status;
                }

                if($this->status == self::STATUS_IN_PROGRESS && $this->task_control == 1 && $this->created_by != Yii::$app->user->id)
                {
                    $this->status = self::STATUS_NEED_ACCEPT;
                    if($this->save())
                        $rtnStatus = $this->status;
                }

                break;

            case self::STATUS_NEED_ACCEPT:
                if($this->status == self::STATUS_IN_PROGRESS)
                {
                    $this->status = self::STATUS_NEED_ACCEPT;
                    if($this->save())
                        $rtnStatus = $this->status;
                }
                break;

            default:
                break;
        }

        return $rtnStatus;
    }

    /**
     * @return array
     */
    public function getAvailableStatusArr()
    {
        $arResult = [];
        switch ($this->status)
        {
            case self::STATUS_OPENED:
                $arResult [] = self::STATUS_IN_PROGRESS;
                break;
            case self::STATUS_IN_PROGRESS:
                $arResult [] = self::STATUS_OPENED;
                $arResult [] = self::STATUS_CLOSE;
                break;
            case self::STATUS_CLOSE:
                $arResult [] = self::STATUS_OPENED;
                break;
            case self::STATUS_NEED_ACCEPT:
                if($this->created_by == Yii::$app->user->id)
                {
                    $arResult [] = self::STATUS_OPENED;
                    $arResult [] = self::STATUS_CLOSE;
                }else{
                    $arResult [] = self::STATUS_OPENED;
                }
                break;
            default:
                break;
        }

        return $arResult;
    }


    /**
     * Сохраняем задчу
     * @param $iUserID
     * @return bool
     * @throws \yii\db\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function createTask($iUserID)
    {
        $tr = Yii::$app->db->beginTransaction(); //транзакция так как испоьзуем несколько моделей
            $bNewRecord = $this->isNewRecord;   //если добавляем новую задачу
            if($this->save()) //сохраняем задачу
            {
                if($this->repeat_task)
                {
                    $obRepeat = new CrmTaskRepeat();
                    $obRepeat->task_id = $this->id;
                    $obRepeat->useRepeatTask = TRUE;
                    if($obRepeat->load(Yii::$app->request->post()) && $obRepeat->save())
                    {

                    }else{
                        $tr->rollBack();    //если были ошибки откатим базу и вернем FALSE
                        return FALSE;
                    }
                }
                if($bNewRecord)
                    if($this->addFiles()) //добавляем файлы при создании задачи
                    {
                        $tr->rollBack();    //если были ошибки откатим базу и вернем FALSE
                        return FALSE;
                    }

                $arBUIDs = [$iUserID,$this->assigned_id]; //пользователя для которых добавляется диалог
                //соисполнители.
                if(!empty($this->arrAcc))
                {
                    foreach($this->arrAcc as $key => $value) //проверим, чтобы ответсвенный не был соисполнителем
                        if($value == $this->assigned_id)
                            unset($this->arrAcc[$key]);

                    if(!empty($this->arrAcc)) {
                        $arAcc = BUser::find()->where(['id' => $this->arrAcc])->all(); //находим всех соисполнитлей
                        if ($arAcc) {
                            foreach ($arAcc as $obAcc)
                            {
                                $this->link('busersAccomplices', $obAcc);
                                $arBUIDs []  = $obAcc->id;
                            }
                        }
                    }
                }

                //наблюдатели.
                if(!empty($this->arrWatch))
                {
                    foreach($this->arrWatch as $key => $value) //проверим, чтобы наблюдатель не был соисполнителем
                        if($value == $this->assigned_id)
                            unset($this->arrWatch[$key]);

                    if(!empty($this->arrWatch)) {
                        $arWatch = BUser::find()->where(['id' => $this->arrWatch])->all(); //находим всех соисполнитлей
                        if ($arWatch ) {
                            foreach ($arWatch  as $obWatch)
                            {
                                $this->link('busersWatchers', $obWatch);
                                $arBUIDs []  = $obWatch->id;
                            }
                        }
                    }
                }

                /** @var Dialogs $obDialog */
                /*
                $obDialog = new Dialogs();  //новый диалог
                $obDialog->type = Dialogs::TYPE_TASK;
                $obDialog->crm_task_id = $this->id;
                $obDialog->buser_id = $iUserID; //кто создал
                $obDialog->status = Dialogs::PUBLISHED; //публикуем диалог
                $obDialog->theme = Yii::t('app/crm','Task').' "'.Html::a($this->title,['/crm/task/view','id' => $this->id],['class' => 'dialog-title-link']).'"';

                if(!empty($this->cmp_id))  //если выбрана компания, то привяжем диалог к компания
                    $obDialog->crm_cmp_id = $this->cmp_id;

                $obContact = NULL;
                if(!empty($this->contact_id))  //если выбран контакт, то привяжем диалог к контакту
                {
                    $obContact = CrmCmpContacts::find()
                        ->select(['cmp_id'])
                        ->where(['id' => $this->contact_id])
                        ->one();   //находим контакт

                    if($obContact && !empty($obContact->cmp_id))    //нашли контак, проверим не привязан ли контакт к компании
                    {
                        $obDialog->crm_cmp_id = $obContact->cmp_id; //привяжем диалог к компании контакта
                    }
                    $obDialog->crm_cmp_contact_id = $this->contact_id; //привяжем диалог к контакту
                }

                */

                $obDialog = $this->createDialogForTask($iUserID);
                if(!$obDialog)
                {
                    $tr->rollBack();    //если были ошибки откатим базу и вернем FALSE
                    return FALSE;
                }else{
                    DialogManager::addMessageToDialog($obDialog->id,Yii::$app->user->id,Yii::t('app/crm',
                        'User {user} create new task',[
                            'user' => Yii::$app->user->identity->getFio()
                        ]
                        ),Messages::YES);
                }

                $arBUIDs = array_unique($arBUIDs);
                $arBUIDs = array_filter($arBUIDs);
                $postModel = new BuserToDialogs(); //привязываем диалог к пользователям
                $rows = [];
                foreach ($arBUIDs as $id) {
                    $rows [] = [$id, $obDialog->id];
                }

                //групповое добавление
                if (Yii::$app->db->createCommand()
                    ->batchInsert(BuserToDialogs::tableName(), $postModel->attributes(), $rows)
                    ->execute())
                {
                    $tr->commit();
                    return TRUE;
                }
            }

        $tr->rollBack();
        return FALSE;
    }

    /**
     * @param $iUserID
     * @return Dialogs|null
     */
    protected function createDialogForTask($iUserID)
    {
        /** @var Dialogs $obDialog */
        $obDialog = new Dialogs();  //новый диалог
        $obDialog->type = Dialogs::TYPE_TASK;
        $obDialog->crm_task_id = $this->id;
        $obDialog->buser_id = $iUserID; //кто создал
        $obDialog->status = Dialogs::PUBLISHED; //публикуем диалог
        $obDialog->theme = Yii::t('app/crm','Task').$this->title;

        if(!empty($this->cmp_id))  //если выбрана компания, то привяжем диалог к компания
            $obDialog->crm_cmp_id = $this->cmp_id;

        $obContact = NULL;
        if(!empty($this->contact_id))  //если выбран контакт, то привяжем диалог к контакту
        {
            /** @var CrmCmpContacts $obContact */
            $obContact = CrmCmpContacts::find()
                ->select(['cmp_id'])
                ->where(['id' => $this->contact_id])
                ->one();   //находим контакт

            if($obContact && !empty($obContact->cmp_id))    //нашли контак, проверим не привязан ли контакт к компании
            {
                $obDialog->crm_cmp_id = $obContact->cmp_id; //привяжем диалог к компании контакта
            }
            $obDialog->crm_cmp_contact_id = $this->contact_id; //привяжем диалог к контакту
        }

        return $obDialog->save() ? $obDialog : NULL;
    }

    /**
     * @return bool
     */
    protected function addFiles()
    {
        $bError = FALSE;
        if(!empty($this->arrFiles))
            {
                $fileInfo  = UploadedFile::getInstances($this, 'arrFiles');
                UploadedFile::reset();  //так как UploadedFile хранит ранее загруженные файлы их нужно сбросить
                foreach($this->arrFiles as $key => $item)
                {
                    if(isset($fileInfo[$key]))
                    {
                        $file = $fileInfo[$key];
                        //@todo дописать функционал UploadBehavior для загрузки нескольких файлов
                        $_FILES['CrmCmpFile'] = [   //костыль формируем массив с файлами, чтобы скормить Uploadbehavior
                            'name' => [
                                'src' => $file->name //'api_manual.doc'
                            ],
                            'type' => [
                                'src' => $file->type//'application/vnd.ms-word'
                            ],
                            'tmp_name' => [
                                'src' => $file->tempName//'/tmp/php6Sgotn'
                            ],
                            'error' => [
                                'src' => $file->error//0
                            ],
                            'size' => [
                                'src' => $file->size//397824
                            ]
                        ];

                        //добавляем файлы. Файлы сохраняются через поведение Uploadbehavior
                        $obFile = new CrmCmpFile();
                        $obFile->task_id = $this->id;
                        $obFile->setScenario('insert');
                        if(!$obFile->save())
                        {
                            $bError = TRUE;
                            break;
                        }
                        UploadedFile::reset();
                    }
                }
            }

        return $bError;
    }

    /**
     * @param null $obDialog
     * @return bool|int
     * @throws \yii\db\Exception
     */
    public function updateUserForDialog($obDialog = NULL)
    {
        if(!$obDialog)
            $obDialog = $this->dialog;

        if(!$obDialog)
            return FALSE;

        $arUser = []; //все пользователи, которые связаны с диалогом.
        $arUser [] = (int)$this->assigned_id;    //текущий ответсвенный
        $arUser [] = (int)$this->created_by;     //кто добавил задачу
        $arObAcc = $this->crmTaskAccomplices;
        if(!empty($arObAcc))
            foreach($arObAcc as $obAcc)
                $arUser [] = (int)$obAcc->buser_id;
        $arObWatch = $this->crmTaskWatchers;
        if(!empty($arObWatch))
            foreach($arObWatch as $obWatch)
                $arUser [] = (int)$obWatch->buser_id;

        if(empty($arUser))  //если пользователй нет, удалим всех
            BUser::deleteAll('dialog_id = :dID',[':dID' => $obDialog->id]);
        else{
            $strU = implode(',',$arUser);
            $arUsersForDelTMP = BuserToDialogs::find()->where('dialog_id = :dID AND buser_id not in ('.$strU.')',[
                ':dID' => $obDialog->id
            ])->all();  //пользователи которых нужно удалить

            if(!empty($arUsersForDelTMP))
            {
                $arDelU = [];
                foreach($arUsersForDelTMP as $del)
                    $arDelU [] = $del->buser_id;

                RedisNotification::removeDialogFromListForUsers($arDelU,$obDialog->id);    //удалим из редис оповещение для удаляемых пользователей
                BuserToDialogs::deleteAll(['buser_id' => $arDelU,'dialog_id' => $obDialog->id]); //удаляем тех, кто не связан с задачей
            }
            unset($arUsersForDelTMP);
            $arExistsTmp = BuserToDialogs::find()->select('buser_id')->where(['dialog_id' => $obDialog->id])->all();
            $arExist = [];
            foreach($arExistsTmp as $exist)
            {
                $arExist [] = $exist->buser_id;
            }

            $arBUIDs = [];
            foreach($arUser as $user)
            {
                if(!in_array($user,$arExist))
                    $arBUIDs []= $user;
            }
            $arBUIDs = array_unique($arBUIDs);
            if(!empty($arBUIDs))
            {
                $postModel = new BuserToDialogs(); //привязываем диалог к пользователям
                $rows = [];
                foreach ($arBUIDs as $id) {
                    $rows [] = [$id, $obDialog->id];
                }

                //групповое добавление
                return Yii::$app->db->createCommand()
                    ->batchInsert(BuserToDialogs::tableName(), $postModel->attributes(), $rows)
                    ->execute();
            }
        }

        return false;
    }

    /**
     *
     */
    public function callTriggerUpdateDialog()
    {
        $this->trigger(self::EVENT_UPDATE_DIALOG);
    }

    /**
     * @return bool
     */
    public function updateUpdatedAt()
    {
        return $this->touch('updated_at');
    }

    /**
     * @param $id
     * @return int
     */
    public static function updateUpdatedAtById($id)
    {
        return self::updateAll(['updated_at' => time()],['id' => $id]);
    }
}
