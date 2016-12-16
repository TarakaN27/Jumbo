<?php

namespace app\modules\crm\controllers;

use backend\models\BUser;
use common\components\helpers\CustomHelper;
use common\components\managers\DialogManager;
use common\components\notification\RedisNotification;
use common\models\BuserToDialogs;
use common\models\CrmCmpContacts;
use common\models\CrmCmpFile;
use common\models\CrmTaskAccomplices;
use common\models\CrmTaskLogTime;
use common\models\CrmTaskRepeat;
use common\models\CrmTaskWatcher;
use common\models\Dialogs;
use common\models\managers\CUserCrmRulesManager;
use Yii;
use common\models\CrmTask;
use common\models\search\CrmTaskSearch;
use backend\components\AbstractBaseBackendController;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\web\ServerErrorHttpException;

/**
 * TaskController implements the CRUD actions for CrmTask model.
 */
class TaskController extends AbstractBaseBackendController
{
    /**
     * переопределяем права на контроллер и экшены
     * @return array
     */
    public function behaviors()
    {
        $tmp = parent::behaviors();
        $tmp['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['moder', 'bookkeeper', 'admin', 'user']
                ]
            ]
        ];
        return $tmp;
    }

    /**
     * Lists all CrmTask models.
     * @return mixed
     */
    public function actionIndex($viewType = NULL)
    {
        $searchModel = new CrmTaskSearch();

        //сохраним выбор пользователя
        $query = Yii::$app->request->queryParams;
        $key = 'task_search_' . Yii::$app->user->id;
        if (
            empty($query) ||
            (!empty($query) && count($query) == 1 && isset($query['viewType'])) ||
            (!empty($query) && count($query) == 1 && isset($query['sort'])) ||
            (!empty($query) && count($query) == 1 && isset($query['page']))// ||
            //    (!empty($query) && count($query) == 1 && isset($query['per-page']))

        ) {
            $tmp = Yii::$app->session->get($key);
            if (!empty($tmp))
                $query = $tmp;
        } else {
            Yii::$app->session->set($key, $query);
        }

        $key_view = 'task_vt_' . Yii::$app->user->id;
        if (is_null($viewType)) {
            $tmp = Yii::$app->session->get($key_view);
            if (!empty($tmp))
                $viewType = $tmp;
            else
                $viewType = CrmTaskSearch::VIEW_TYPE_ALL;
        } else {
            Yii::$app->session->set($key_view, $viewType);
        }
        $dataProvider = $searchModel->search($query, $viewType, NULL, [], TRUE);

        $arNewTasks = RedisNotification::getNewTaskList(Yii::$app->user->id); //получаем новые задачи

        // Get the initial city description
        $cuserDesc = empty($searchModel->cmp_id) ? '' : \common\models\CUser::findOne($searchModel->cmp_id)->getInfoWithSite();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'viewType' => $viewType,
            'arNewTasks' => $arNewTasks,
            'cuserDesc' => $cuserDesc
        ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        /** @var CrmTask $model */
        $model = CrmTask::find()
            ->with(
                'cmp', 'contact', 'parent',
                'busersAccomplices', 'busersWatchers',
                'taskFiles', 'cmp.quantityHour'
            )
            ->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $model->callViewedEvent();

        $arAccompl = $model->busersAccomplices; //помогают
        $arWatchers = $model->busersWatchers; //наблюдают
        $arFile = $model->taskFiles; //файлы
        $obCmp = $model->cmp;
        $obCnt = $model->contact;

        $obAccmpl = new CrmTaskAccomplices(); // модель для сооисполнитлей
        $obAccmpl->task_id = (int)$id;

        $obWatcher = new CrmTaskWatcher(); //наблюдатели
        $obWatcher->task_id = (int)$id;

        $obLogWork = new CrmTaskLogTime(['log_date' => date('d.m.Y', time()), 'task_id' => $model->id]); //модель для добавления времени
        $obLogWork->setScenario(CrmTaskLogTime::SCENARIO_LOG_TIME);

        $obFile = new CrmCmpFile(); //модель для добавления файлов
        $obFile->setScenario('insert');
        $obFile->task_id = $id;

        $obTaskRepeat = new CrmTaskRepeat();    //task repeat parameters
        $obTaskRepeat->initForCreate();

        //Модель для задач
        $modelTask = new CrmTask();
        //дефолтные состояния
        $modelTask->created_by = Yii::$app->user->id;  //кто создал задачу
        //$modelTask->assigned_id = Yii::$app->user->id; //по умолчанию вешаем сами на себя
        $modelTask->status = CrmTask::STATUS_OPENED; //статус. По умолчанию открыта
        $modelTask->cmp_id = $model->cmp_id;   //вешаем компанию
        $modelTask->contact_id = $model->contact_id; //вешаем конаткт
        $modelTask->parent_id = $model->id;
        $modelTask->task_control = CrmTask::YES;    //принять после выполнения по-умолчанию
        $modelTask->repeat_task = CrmTask::NO;

        $obTime = CrmTaskLogTime::find()->where([ //занесенное время
            'task_id' => $model->id,
        ])->all();
        $timeBegined = NULL;
        $timeSpend = 0;
        $obLogBegin = NULL;
        $obLog = [];
        /** @var CrmTaskLogTime $time */
        foreach ($obTime as $time) {
            $timeSpend += (int)$time->spend_time;
            $obLog[] = $time;
        }

        /**
         * Соисполнитель
         */
        $arAccomplIds = [];
        foreach ($arAccompl as $item)
            $arAccomplIds[] = $item->id;
        if ($obAccmpl->load(Yii::$app->request->post())) {
            if (in_array($obAccmpl->buser_id, $arAccomplIds)) {
                Yii::$app->session->setFlash('error', Yii::t('app/crm', 'You are trying to add user, witch already accomplices'));
                return $this->redirect(['view', 'id' => $id]);
            }

            if ($obAccmpl->save()) {
                $model->updateUpdatedAt();
                $model->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
                Yii::$app->session->setFlash('error', Yii::t('app/crm', 'Accomplice successfully added'));
                return $this->redirect(['view', 'id' => $id]);
            }

            Yii::$app->session->setFlash('error', Yii::t('app/crm', 'Can not add accomplice'));
            return $this->redirect(['view', 'id' => $id]);
        }
        /**
         * Наблюдатели
         */
        $arWatchIDs = [];
        foreach ($arWatchers as $watch)
            $arWatchIDs[] = $watch->id;
        if ($obWatcher->load(Yii::$app->request->post())) {
            if (in_array($obWatcher->buser_id, $arWatchIDs)) {
                Yii::$app->session->setFlash('error', Yii::t('app/crm', 'You are trying to add user, witch already watching'));
                return $this->redirect(['view', 'id' => $id]);
            }

            if ($obWatcher->save()) {
                $model->updateUpdatedAt();
                $model->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
                Yii::$app->session->setFlash('success', Yii::t('app/crm', 'Watcher successfully added'));
                return $this->redirect(['view', 'id' => $id]);
            }

            Yii::$app->session->setFlash('error', Yii::t('app/crm', 'Can not add watcher'));
            return $this->redirect(['view', 'id' => $id]);
        }



        /**
         * Добавление задачи
         */
        if ($modelTask->load(Yii::$app->request->post()) && $modelTask->validate()) {
            $validRepeat = TRUE;
            if ($modelTask->repeat_task) {
                if (!$obTaskRepeat->load(Yii::$app->request->post()) || ($obTaskRepeat->load(Yii::$app->request->post()) && !$obTaskRepeat->validate())) {
                    $validRepeat = FALSE;
                }
            }
            if ($validRepeat && $modelTask->createTask(Yii::$app->user->id)) {
                Yii::$app->session->addFlash('success', Yii::t('app/crm', 'Sub task successfully added'));
                return $this->redirect(['view', 'id' => $id, '#' => 'tab_content5']);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app/crm', 'Error. Can not add new task'));
                return $this->redirect(['view', 'id' => $id]);
            }
        }

        /**
         * Добавление файла
         */

        if (Yii::$app->request->isPost) {
            if(count($files = CrmTask::addFiles($id))>0){
                $model->updateUpdatedAt();
                DialogManager::actionLoadFileToTask($model->dialog,$files);
                Yii::$app->session->setFlash('success', Yii::t('app/crm', 'File successfully added'));
                return $this->redirect(['view', 'id' => $id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app/crm', 'Error. Can not add file'));
                return $this->redirect(['view', 'id' => $id]);
            }
        }

        $obParent = $model->parent;
        $arChild = $model->childTask;
        $sAssName = empty($modelTask->assigned_id) ? '' : BUser::findOne($modelTask->assigned_id)->getFio();

        $queryChild = CrmTask::find()
            ->alias('t')
            ->select([
                't.id',
                't.title',
                't.status',
                't.deadline',
                't.priority',
                'assigned_id',
                'as.fname',
                'as.mname',
                'as.lname'
            ])
            ->where(['parent_id' => $id])
            ->joinWith('assigned as');


        $dataProviderChildtask = new ActiveDataProvider([
            'query' => $queryChild,
            'sort' => [
                'defaultOrder' => ['status' => SORT_ASC]
            ],
        ]);
        $dataWatchers = [];
        return $this->render('view', [
            'model' => $this->findModel($id),
            'obAccmpl' => $obAccmpl,
            'arAccmpl' => $arAccompl,
            'obWatcher' => $obWatcher,
            'arWatchers' => $arWatchers,
            'timeBegined' => $timeBegined,
            'timeSpend' => $timeSpend,
            'obLogBegin' => $obLogBegin,
            'obLog' => $obLog,
            'obLogWork' => $obLogWork,
            'obFile' => $obFile,
            'arFile' => $arFile,
            'obCmp' => $obCmp,
            'obCnt' => $obCnt,
            'obParent' => $obParent,
            'arChild' => $arChild,
            'sAssName' => $sAssName,
            'modelTask' => $modelTask,
            'dataProviderChildtask' => $dataProviderChildtask,
            'dataWatchers' => $dataWatchers,
            'obTaskRepeat' => $obTaskRepeat
        ]);
    }

    /**
     * @param $id
     * @return int|string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionChangeAssigned($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->updateUpdatedAt();
                $model->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
                $obMan = $model->assigned;
                if (!$obMan)
                    return ['fio' => NULL, 'role' => NULL];
                else
                    return ['fio' => $obMan->getFio(), 'role' => $obMan->getRoleStr()];
            } else {
                throw new ServerErrorHttpException();
            }
        }
        throw new ServerErrorHttpException();
    }

    /**
     * @param $id
     * @return null|string
     */
    public function actionLoadSubtaskTime($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $arTaskID = (new Query())
            ->select('id')
            ->from(CrmTask::tableName())
            ->where(['parent_id' => $id])
            ->all();

        if (empty($arTaskID))
            return $this->renderPartial('part/_woked_time_area', [
                'obLog' => [],
                'showTaskID' => TRUE
            ]);

        $arIds = [];
        foreach ($arTaskID as $task)
            $arIds[] = $task['id'];

        $arLogs = CrmTaskLogTime::find()->where(['task_id' => $arIds])->all();

        return $this->renderPartial('part/_woked_time_area', [
            'obLog' => $arLogs,
            'showTaskID' => TRUE
        ]);
    }


    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionBeginTask()
    {
        $data = $this->changeStatus(CrmTask::STATUS_IN_PROGRESS);
        return $this->returnJsonHelper($data);
    }

    /**
     * @param $iStatus
     * @param null $obTask
     * @return array
     * @throws NotFoundHttpException
     */
    protected function changeStatus($iStatus, $obTask = NULL)
    {
        if (is_null($obTask)) {
            $tID = Yii::$app->request->post('tID');
            $iUser = Yii::$app->user->id;
            if (empty($tID) || empty($iUser))
                throw new InvalidParamException('Task id and user id is required');
            /** @var CrmTask $obTask */
            $obTask = CrmTask::findOne($tID);
            if (!$obTask)
                throw new NotFoundHttpException();
        }

        $iNewStatus = $obTask->changeTaskStatus($iStatus);
        if ($iNewStatus)
            $data = [
                'code' => $obTask->status,
                'text' => $obTask->getStatusStr()
            ];
        else
            $data = [
                'code' => NULL,
                'text' => $obTask->getStatusStr()
            ];

        return $data;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionPauseTask()
    {
        $data = $this->changeStatus(CrmTask::STATUS_OPENED);
        return $this->returnJsonHelper($data);
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDoneTask()
    {
        $iTID = Yii::$app->request->post('tID');
        /** @var CrmTask $obTask */
        $obTask = CrmTask::findOne($iTID); //находим задачу
        if (!$obTask)
            throw new NotFoundHttpException('Task not found');

        if ($obTask->task_control == 1 && $obTask->created_by != Yii::$app->user->id) {
            $data = $this->changeStatus(CrmTask::STATUS_NEED_ACCEPT, $obTask);
        } else {
            $data = $this->changeStatus(CrmTask::STATUS_CLOSE, $obTask);
        }

        return $this->returnJsonHelper($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function returnJsonHelper($data)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

    /**
     * Creates a new CrmTask model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $iUserID = Yii::$app->user->id;
        $model = new CrmTask();
        $model->repeat_task = CrmTask::NO;      //set default value to NO
        //дефолтные состояния
        $model->created_by = $iUserID;  //кто создал задачу
        //$model->assigned_id = $iUserID; //по умолчанию вешаем сами на себя
        $model->status = CrmTask::STATUS_OPENED; //статус
        $model->task_control = CrmTask::YES;    //принять после выполнения по-умолчанию
        $data = [];
        $obFile = new CrmCmpFile();
        $obTaskRepeat = new CrmTaskRepeat();    //task repeat parameters
        $obTaskRepeat->initForCreate();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) { //грузим и валидируем
            $validRepeat = TRUE;
            if ($model->repeat_task) {
                if (!$obTaskRepeat->load(Yii::$app->request->post()) || ($obTaskRepeat->load(Yii::$app->request->post()) && !$obTaskRepeat->validate())) {
                    $validRepeat = FALSE;
                }
            }
            if ($validRepeat && $model->createTask($iUserID)) {
                Yii::$app->session->addFlash('success', Yii::t('app/crm', 'Task successfully added'));
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app/crm', 'Error. Can not add new task'));
            }
        }

        $sAssName = empty($model->assigned_id) ? '' : BUser::findOne($model->assigned_id)->getFio();
        if (!empty($model->cmp_id))
            $cuserDesc = \common\models\CUser::findOne($model->cmp_id)->getInfo();
        else
            $cuserDesc = '';

        if (!empty($model->contact_id))
            $contactDesc = \common\models\CrmCmpContacts::findOne($model->contact_id)->fio;
        else
            $contactDesc = '';

        $pTaskName = '';
        if (!empty($model->parent_id)) {
            $obTask = CrmTask::findOne($model->parent_id);
            if (!$obTask)
                $pTaskName = $obTask->id . ' - ' . CustomHelper::cuttingString($obTask->title, 100);
        }

        $dataWatchers = [];
        if (!empty($model->arrWatch)) {
            $obWatchers = BUser::find()
                ->select(['id', 'fname', 'mname', 'lname'])
                ->where(['id' => $model->arrWatch])
                ->all();
            $dataWatchers = ArrayHelper::map($obWatchers, 'id', 'fio');
        }


        return $this->render('create', [
            'model' => $model,
            'sAssName' => $sAssName,
            'cuserDesc' => $cuserDesc,
            'contactDesc' => $contactDesc,
            'data' => $data,
            'dataWatchers' => $dataWatchers,
            'obFile' => $obFile,
            'pTaskName' => $pTaskName,
            'obTaskRepeat' => $obTaskRepeat
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->created_by != Yii::$app->user->id && !Yii::$app->user->can('adminRights')) //редактировать задачу может только автор
            throw new ForbiddenHttpException();

        if (!empty($model->deadline))
            $model->deadline = Yii::$app->formatter->asDatetime($model->deadline);
        $arAccOb = $model->busersAccomplices;
        $arWatchers = $model->busersWatchers;
        if (!empty($arWatchers))
            foreach ($arWatchers as $watcher)
                $model->arrWatch[] = $watcher->id;

        $arAccObOld = [];
        $data = [];
        if ($arAccOb)
            foreach ($arAccOb as $acc) {
                $model->arrAcc [] = $acc->id;
                $arAccObOld [] = $acc->id;
                $data[$acc->id] = $acc->getFio();
            }

        if ($model->repeat_task) {
            /** @var CrmTaskRepeat $obTaskRepeat */
            $obTaskRepeat = $model->repeatTask;
            if (!$obTaskRepeat)
                throw new NotFoundHttpException('Repeat task params not found');

            $obTaskRepeat->start_date = Yii::$app->formatter->asDate($obTaskRepeat->start_date);
            if (!empty($obTaskRepeat->end_date))
                $obTaskRepeat->end_date = Yii::$app->formatter->asDate($obTaskRepeat->end_date);
        } else {
            $obTaskRepeat = new CrmTaskRepeat();    //task repeat parameters
            $obTaskRepeat->initForCreate();
        }


        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $validRepeat = TRUE;
            if ($model->repeat_task) {
                if (!$obTaskRepeat->load(Yii::$app->request->post()) || ($obTaskRepeat->load(Yii::$app->request->post()) && !$obTaskRepeat->validate())) {
                    $validRepeat = FALSE;
                }
            }

            $transaction = Yii::$app->db->beginTransaction();
            if ($model->isAttributeChanged('repeat_task') && $model->repeat_task != CrmTask::YES) {
                CrmTaskRepeat::deleteAll(['task_id' => $model->id]);
            }

            if ($validRepeat && $model->save()) {
                $saveRepeat = TRUE;
                if ($model->repeat_task) {
                    if ($obTaskRepeat->isNewRecord)
                        $obTaskRepeat->task_id = $model->id;
                    $saveRepeat = $obTaskRepeat->save();
                }
                if ($saveRepeat) {
                    $model->unlinkAll('busersAccomplices', TRUE);
                    $arAccNew = [];
                    if (!empty($model->arrAcc)) {
                        //соисполнители.
                        foreach ($model->arrAcc as $key => $value) //проверим, чтобы ответсвенный не был соисполнителем
                            if ($value == $model->assigned_id)
                                unset($model->arrAcc[$key]);

                        if (!empty($model->arrAcc)) {
                            $arAcc = BUser::find()->where(['id' => $model->arrAcc])->all(); //находим всех соисполнитлей
                            if ($arAcc) {
                                foreach ($arAcc as $obAcc) {
                                    $arAccNew[] = $obAcc->id;
                                    $model->link('busersAccomplices', $obAcc);
                                }

                            }
                        }
                    }
                    $model->unlinkAll('busersWatchers', TRUE);
                    //наблюдатели.
                    if (!empty($model->arrWatch)) {
                        foreach ($model->arrWatch as $key => $value) //проверим, чтобы наблюдатель не был соисполнителем
                            if ($value == $model->assigned_id)
                                unset($model->arrWatch[$key]);

                        if (!empty($model->arrWatch)) {
                            $arWatch = BUser::find()->where(['id' => $model->arrWatch])->all(); //находим всех соисполнитлей
                            if ($arWatch) {
                                foreach ($arWatch as $obWatch) {
                                    $model->link('busersWatchers', $obWatch);
                                }
                            }
                        }
                    }

                    //нужно у удаленных соисполнителелй удалить балуны
                    $arAccDiff = array_diff($arAccObOld, $arAccNew);
                    if (!empty($arAccDiff))
                        RedisNotification::removeNewTaskFromList($arAccDiff, $model->id);
                    $modelUpd = $this->findModel($model->id);
                    $modelUpd->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
            $transaction->rollBack();
        }

        $sAssName = BUser::findOne($model->assigned_id)->getFio();
        if (!empty($model->cmp_id))
            $cuserDesc = \common\models\CUser::findOne($model->cmp_id)->getInfo();
        else
            $cuserDesc = '';
        if (!empty($model->contact_id))
            $contactDesc = \common\models\CrmCmpContacts::findOne($model->contact_id)->fio;
        else
            $contactDesc = '';

        $pTaskName = '';
        if (!empty($model->parent_id)) {
            $obTask = CrmTask::findOne($model->parent_id);
            if ($obTask)
                $pTaskName = $obTask->id . ' - ' . CustomHelper::cuttingString($obTask->title, 100);
        }

        $dataWatchers = [];
        if (!empty($model->arrWatch)) {
            $obWatchers = BUser::find()
                ->select(['id', 'fname', 'mname', 'lname'])
                ->where(['id' => $model->arrWatch])
                ->all();
            $dataWatchers = ArrayHelper::map($obWatchers, 'id', 'fio');
        }

        return $this->render('update', [
            'model' => $model,
            'cuserDesc' => $cuserDesc,
            'contactDesc' => $contactDesc,
            'sAssName' => $sAssName,
            'data' => $data,
            'pTaskName' => $pTaskName,
            'dataWatchers' => $dataWatchers,
            'obTaskRepeat' => $obTaskRepeat
        ]);

    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->created_by != Yii::$app->user->id && !Yii::$app->user->can('adminRights')) //удалить задачу может только тот кто создал и админ
            throw new ForbiddenHttpException();

        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the CrmTask model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CrmTask the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CrmTask::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionOpenTask()
    {
        $data = $this->changeStatus(CrmTask::STATUS_OPENED);
        return $this->returnJsonHelper($data);
    }

    /**
     * @return array
     */
    public function actionSendLogWork()
    {
        $obLog = new CrmTaskLogTime();
        $obLog->setScenario(CrmTaskLogTime::SCENARIO_LOG_TIME);
        $obLog->buser_id = Yii::$app->user->id;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; //указываем что возвращаем json
        if ($obLog->load(Yii::$app->request->post()) && $obLog->save()) {
            $obTimeArr = CrmTaskLogTime::find()->where([
                'task_id' => $obLog->task_id,
            ])->all();

            $timeSpend = 0;

            /** @var CrmTaskLogTime $time */
            foreach ($obTimeArr as $time) {
                $timeSpend += (int)$time->spend_time;
            }
            return [
                'error' => NULL,
                'model' => $obLog,
                'content' => $this->renderPartial('part/_woked_time_area', ['obLog' => $obTimeArr, 'disableHidden' => TRUE]),
                'timeSpend' => \common\components\helpers\CustomHelper::getFormatedTaskTime($timeSpend)
            ];
        }

        return ['error' => $obLog->getErrors(), 'model' => NULL, 'content' => NULL, 'timeSpend' => NULL];
    }

    /**
     * @param null $id
     * @return array|string
     * @throws NotFoundHttpException
     */
    public function actionUpdateLogTime($id = NULL)
    {
        if (is_null($id))
            $id = Yii::$app->request->post('id');
        /** @var CrmTaskLogTime $model */
        $model = CrmTaskLogTime::findOne(['id' => $id, 'buser_id' => Yii::$app->user->id]);
        if (!$model)
            throw new NotFoundHttpException();

        $model->setScenario(CrmTaskLogTime::SCENARIO_UPDATE);
        if (!empty($model->log_date))
            $model->log_date = Yii::$app->formatter->asDate($model->log_date);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $obTimeArr = CrmTaskLogTime::find()->where([
                'task_id' => $model->task_id,
            ])->all();

            $timeSpend = 0;

            /** @var CrmTaskLogTime $time */
            foreach ($obTimeArr as $time) {
                $timeSpend += (int)$time->spend_time;
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'error' => NULL,
                'model' => $model,
                'content' => $this->renderPartial('part/_woked_time_area', ['obLog' => $obTimeArr]),
                'timeSpend' => \common\components\helpers\CustomHelper::getFormatedTaskTime($timeSpend)
            ];
        }
        $model->covertSecondsToTime();
        return $this->renderAjax('part/log_time_form', [
            'model' => $model
        ]);

    }

    /**
     * @param $id
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionDownloadFile($id)
    {
        /** @var CrmCmpFile $obFile */
        $obFile = CrmCmpFile::findOne(['id' => $id]);
        if (!$obFile)
            throw new NotFoundHttpException('File not found');
        return Yii::$app->response->sendFile($obFile->getFilePath(), $obFile->name . '.' . $obFile->ext);
    }

    /**
     * @return false|int
     * @throws NotFoundHttpException
     */
    public function actionDeleteFile()
    {
        $pk = Yii::$app->request->post('pk');
        $obFile = CrmCmpFile::findOne($pk);
        if (!$obFile)
            throw new NotFoundHttpException('File not found');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $obFile->delete();
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\ExitException
     */
    public function actionUpdateStatus()
    {
        $pk = Yii::$app->request->post('pk');
        $value = Yii::$app->request->post('value');

        $obTask = CrmTask::findOne($pk);
        if (!$obTask)
            throw new NotFoundHttpException('Task not found');

        if (!$obTask->changeTaskStatus((int)$value))
            throw new ServerErrorHttpException('Error');

        Yii::$app->end(200);
    }

    public function actionSaveDeadline()
    {
        $pk = Yii::$app->request->post('pk');
        $date = Yii::$app->request->post('value');
        $obTask = CrmTask::findOne($pk);
        if (!$obTask)
            throw new NotFoundHttpException('Task not found');

        if ($obTask->created_by != Yii::$app->user->id && !Yii::$app->user->can('adminRights')) //редактировать задачу может только автор
            throw new ForbiddenHttpException();
        $obTask->deadline = $date;
        $obTask->save();
        Yii::$app->end(200);
    }

    public function actionUploadFile()
    {
        $_FILES['CrmCmpFile'] = [   //костыль формируем массив с файлами, чтобы скормить Uploadbehavior
            'name' => [
                'src' => $_FILES['file']['name']
            ],
            'type' => [
                'src' => $_FILES['file']['type']
            ],
            'tmp_name' => [
                'src' => $_FILES['file']['tmp_name']
            ],
            'error' => [
                'src' => $_FILES['file']['error']
            ],
            'size' => [
                'src' => $_FILES['file']['size']
            ]
        ];
        $obFile = new CrmCmpFile();
        $obFile->setScenario('insert');
        if (!$obFile->save()) {
            throw new ServerErrorHttpException('Error');
        }
        return $obFile->id;

    }

    public function actionFileDelete()
    {
        $id = Yii::$app->request->post('id');
        $file = CrmCmpFile::findOne($id);
        if ($file) {
            $file->delete();
        }

        die;
    }
}
