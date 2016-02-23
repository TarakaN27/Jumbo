<?php

namespace app\modules\crm\controllers;

use backend\models\BUser;
use common\components\notification\RedisNotification;
use common\models\BuserToDialogs;
use common\models\CrmCmpContacts;
use common\models\CrmCmpFile;
use common\models\CrmTaskAccomplices;
use common\models\CrmTaskLogTime;
use common\models\CrmTaskWatcher;
use common\models\Dialogs;
use common\models\managers\CUserCrmRulesManager;
use Yii;
use common\models\CrmTask;
use common\models\search\CrmTaskSearch;
use backend\components\AbstractBaseBackendController;
use yii\base\InvalidParamException;
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
                    'roles' => ['moder','bookkeeper','admin','user']
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
        $key = 'task_search_'.Yii::$app->user->id;
        if(
            empty($query) ||
            (!empty($query) && count($query) == 1 && isset($query['viewType'])) ||
            (!empty($query) && count($query) == 1 && isset($query['sort'])) ||
            (!empty($query) && count($query) == 1 && isset($query['page']))// ||
        //    (!empty($query) && count($query) == 1 && isset($query['per-page']))

        )
        {
            $tmp = Yii::$app->session->get($key);
            if(!empty($tmp))
                $query = $tmp;
        }else{
            Yii::$app->session->set($key,$query);
        }

        $key_view = 'task_vt_'.Yii::$app->user->id;
        if(is_null($viewType))
        {
            $tmp = Yii::$app->session->get($key_view);
            if(!empty($tmp))
                $viewType = $tmp;
            else
                $viewType = CrmTaskSearch::VIEW_TYPE_ALL;
        }else{
            Yii::$app->session->set($key_view,$viewType);
        }


        $dataProvider = $searchModel->search($query,$viewType,NULL,[],TRUE);

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
        $model = CrmTask::find()
                 ->with(
				  'cmp','contact',
				  'busersAccomplices','busersWatchers',
				  'taskFiles','cmp.quantityHour'
			  )
            ->where(['id' => $id])->one();
        if (!$model){
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

        $obLogWork = new CrmTaskLogTime(['log_date' => date('Y-m-d',time()),'task_id' => $model->id]); //модель для добавления времени
        $obLogWork->setScenario(CrmTaskLogTime::SCENARIO_LOG_TIME);

        $obFile = new CrmCmpFile(); //модель для добавления файлов
        $obFile->setScenario('insert');
        $obFile->task_id = $id;


        $obTime = CrmTaskLogTime::find()->where([ //занесенное время
            'task_id' => $model->id,
        ])->all();
        $timeBegined = NULL;
        $timeSpend = 0;
        $obLogBegin = NULL;
        $obLog = [];
        /** @var CrmTaskLogTime $time */
        foreach($obTime as $time)
        {
            $timeSpend +=(int)$time->spend_time;
            $obLog[] = $time;
        }

        /**
         * Соисполнитель
         */
        $arAccomplIds = [];
        foreach($arAccompl as $item)
            $arAccomplIds[] = $item->id;
        if($obAccmpl->load(Yii::$app->request->post()))
        {
            if(in_array($obAccmpl->buser_id,$arAccomplIds))
            {
                Yii::$app->session->setFlash('error',Yii::t('app/crm','You are trying to add user, witch already accomplices'));
                return $this->redirect(['view','id' => $id]);
            }

            if($obAccmpl->save())
            {
                $model->updateUpdatedAt();
                $model->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
                Yii::$app->session->setFlash('error',Yii::t('app/crm','Accomplice successfully added'));
                return $this->redirect(['view','id' => $id]);
            }

            Yii::$app->session->setFlash('error',Yii::t('app/crm','Can not add accomplice'));
            return $this->redirect(['view','id' => $id]);
        }
        /**
         * Наблюдатели
         */
        $arWatchIDs = [];
        foreach($arWatchers as $watch)
            $arWatchIDs[] = $watch->id;
        if($obWatcher->load(Yii::$app->request->post()))
        {
            if(in_array($obWatcher->buser_id,$arWatchIDs))
            {
                Yii::$app->session->setFlash('error',Yii::t('app/crm','You are trying to add user, witch already watching'));
                return $this->redirect(['view','id' => $id]);
            }

            if($obWatcher->save())
            {
                $model->updateUpdatedAt();
                $model->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
                Yii::$app->session->setFlash('success',Yii::t('app/crm','Watcher successfully added'));
                return $this->redirect(['view','id' => $id]);
            }

            Yii::$app->session->setFlash('error',Yii::t('app/crm','Can not add watcher'));
            return $this->redirect(['view','id' => $id]);
        }

        /**
         * Смена ответсвенного.
         */
        if($model->load(Yii::$app->request->post()))
        {
            if($model->save())
            {
                $model->updateUpdatedAt();
                $model->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
                Yii::$app->session->setFlash('success',Yii::t('app/crm','Assign successfully changed'));
                return $this->redirect(['view','id' => $id]);
            }else{
                Yii::$app->session->setFlash('success',Yii::t('app/crm','Can not change assign'));
                return $this->redirect(['view','id' => $id]);
            }
        }

        /**
         * Добавление файла
         */
        if($obFile->load(Yii::$app->request->post()))
        {
            if($obFile->save())
            {
                $model->updateUpdatedAt();
                Yii::$app->session->setFlash('success',Yii::t('app/crm','File successfully added'));
                return $this->redirect(['view','id' => $id]);
            }else{
                Yii::$app->session->setFlash('error',Yii::t('app/crm','Error. Can not add file'));
                return $this->redirect(['view','id' => $id]);
            }
        }

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
            'obCnt' => $obCnt
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
    protected function changeStatus($iStatus,$obTask = NULL)
    {
        if(is_null($obTask))
        {
            $tID = Yii::$app->request->post('tID');
            $iUser = Yii::$app->user->id;
            if(empty($tID) || empty($iUser))
                throw new InvalidParamException('Task id and user id is required');
            /** @var CrmTask $obTask */
            $obTask = CrmTask::findOne($tID);
            if(!$obTask)
                throw new NotFoundHttpException();
        }

        $iNewStatus = $obTask->changeTaskStatus($iStatus);
        if($iNewStatus)
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
        if(!$obTask)
            throw new NotFoundHttpException('Task not found');

        if($obTask->task_control == 1 && $obTask->created_by != Yii::$app->user->id)
        {
            $data = $this->changeStatus(CrmTask::STATUS_NEED_ACCEPT,$obTask);
        }else{
            $data = $this->changeStatus(CrmTask::STATUS_CLOSE,$obTask);
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
        //дефолтные состояния
        $model->created_by = $iUserID;  //кто создал задачу
        $model->assigned_id = $iUserID; //по умолчанию вешаем сами на себя
        $model->status = CrmTask::STATUS_OPENED; //статус
        $data = [];
        $obFile = new CrmCmpFile();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) { //грузим и валидируем
            if($model->createTask($iUserID))
            {
                Yii::$app->session->addFlash('success',Yii::t('app/crm','Task successfully added'));
                return $this->redirect(['view', 'id' => $model->id]);
            }else{
                Yii::$app->session->setFlash('error',Yii::t('app/crm','Error. Can not add new task'));
            }
        } else {

            $sAssName = BUser::findOne($model->assigned_id)->getFio();
            if(!empty($model->cmp_id))
                $cuserDesc = \common\models\CUser::findOne($model->cmp_id)->getInfo();
            else
                $cuserDesc = '';
            if(!empty($model->contact_id))
                $contactDesc = \common\models\CrmCmpContacts::findOne($model->contact_id)->fio;
            else
                $contactDesc = '';

            return $this->render('create', [
                'model' => $model,
                'sAssName' => $sAssName,
                'cuserDesc' => $cuserDesc,
                'contactDesc' => $contactDesc,
                'data' => $data,
                'obFile' => $obFile
            ]);
        }
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
        if($model->created_by != Yii::$app->user->id) //редактировать задачу может только автор
            throw new ForbiddenHttpException();

        $arAccOb = $model->busersAccomplices;
        $arAccObOld = [];
        $data = [];
        if($arAccOb)
            foreach($arAccOb as $acc) {
                $model->arrAcc [] = $acc->id;
                $arAccObOld [] =  $acc->id;
                $data[$acc->id] = $acc->getFio();
            }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $model->unlinkAll('busersAccomplices',TRUE);
            $arAccNew = [];
            if(!empty($model->arrAcc))
            {
                //соисполнители.
                foreach($model->arrAcc as $key => $value) //проверим, чтобы ответсвенный не был соисполнителем
                    if($value == $model->assigned_id)
                        unset($model->arrAcc[$key]);

                if(!empty($model->arrAcc)) {
                    $arAcc = BUser::find()->where(['id' => $model->arrAcc])->all(); //находим всех соисполнитлей
                    if ($arAcc) {
                        foreach ($arAcc as $obAcc)
                        {
                            $arAccNew[] = $obAcc->id;
                            $model->link('busersAccomplices', $obAcc);
                        }

                    }
                }
            }
            //нужно у удаленных соисполнителелй удалить балуны
            $arAccDiff = array_diff($arAccObOld,$arAccNew);
            if(!empty($arAccDiff))
                RedisNotification::removeNewTaskFromList($arAccDiff,$model->id);

            $model->callTriggerUpdateDialog();  //обновление пользователй причастных к диалогу
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            $sAssName = BUser::findOne($model->assigned_id)->getFio();
            if(!empty($model->cmp_id))
                $cuserDesc = \common\models\CUser::findOne($model->cmp_id)->getInfo();
            else
                $cuserDesc = '';
            if(!empty($model->contact_id))
                $contactDesc = \common\models\CrmCmpContacts::findOne($model->contact_id)->fio;
            else
                $contactDesc = '';
            return $this->render('update', [
                'model' => $model,
                'cuserDesc' => $cuserDesc,
                'contactDesc' => $contactDesc,
                'sAssName' => $sAssName,
                'data' => $data
            ]);
        }
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
        if($model->created_by != Yii::$app->user->id) //удалить задачу может только тот кто создал
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
        if($obLog->load(Yii::$app->request->post()) && $obLog->save())
        {
            $obTimeArr = CrmTaskLogTime::find()->where([
                'task_id' => $obLog->task_id,
            ])->all();

            $timeSpend = 0;

            /** @var CrmTaskLogTime $time */
            foreach($obTimeArr as $time)
            {
                $timeSpend +=(int)$time->spend_time;
            }
            return [
                'error' => NULL,
                'model' => $obLog,
                'content' => $this->renderPartial('part/_woked_time_area',['obLog' => $obTimeArr,'disableHidden' => TRUE]),
                'timeSpend' => \common\components\helpers\CustomHelper::getFormatedTaskTime($timeSpend)
            ];
        }

        return ['error' => $obLog->getErrors(),'model' => NULL,'content' => NULL,'timeSpend' => NULL];
    }

    /**
     * @param null $id
     * @return array|string
     * @throws NotFoundHttpException
     */
    public function actionUpdateLogTime($id = NULL)
    {
        if(is_null($id))
            $id = Yii::$app->request->post('id');
        /** @var CrmTaskLogTime $model */
        $model = CrmTaskLogTime::findOne(['id' => $id,'buser_id' => Yii::$app->user->id]);
        if(!$model)
            throw new NotFoundHttpException();

        $model->setScenario(CrmTaskLogTime::SCENARIO_UPDATE);

        if($model->load(Yii::$app->request->post()) && $model->save())
        {
            $obTimeArr = CrmTaskLogTime::find()->where([
                'task_id' => $model->task_id,
            ])->all();

            $timeSpend = 0;

            /** @var CrmTaskLogTime $time */
            foreach($obTimeArr as $time)
            {
                $timeSpend +=(int)$time->spend_time;
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'error' => NULL,
                'model' => $model,
                'content' => $this->renderPartial('part/_woked_time_area',['obLog' => $obTimeArr]),
                'timeSpend' => \common\components\helpers\CustomHelper::getFormatedTaskTime($timeSpend)
            ];
        }
        $model->covertSecondsToTime();
        return $this->renderAjax('part/log_time_form',[
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
        if(!$obFile)
            throw new NotFoundHttpException('File not found');
        return Yii::$app->response->sendFile($obFile->getFilePath(),$obFile->name.'.'.$obFile->ext);
    }

    /**
     * @return false|int
     * @throws NotFoundHttpException
     */
    public function actionDeleteFile()
    {
        $pk = Yii::$app->request->post('pk');
        $obFile = CrmCmpFile::findOne($pk);
        if(!$obFile)
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
        if(!$obTask)
            throw new NotFoundHttpException('Task not found');

        if(!$obTask->changeTaskStatus((int)$value))
            throw new ServerErrorHttpException('Error');

        Yii::$app->end(200);
    }
}
