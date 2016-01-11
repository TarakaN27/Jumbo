<?php

namespace app\modules\crm\controllers;

use backend\models\BUser;
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
    public function actionIndex($viewType = CrmTaskSearch::VIEW_TYPE_ALL)
    {
        $searchModel = new CrmTaskSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$viewType);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'viewType' => $viewType
        ]);
    }

    /**
     * Displays a single CrmTask model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $arAccompl = $model->busersAccomplices; //помогают
        $arWatchers = $model->busersWatchers; //наблюдают
        $arFile = $model->taskFiles; //файлы

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
        $model->created_by = $iUserID;  //кто создал задачу
        $model->assigned_id = $iUserID; //по умолчанию вешаем сами на себя
        $model->status = CrmTask::STATUS_OPENED; //статус

        if ($model->load(Yii::$app->request->post()) && $model->validate()) { //грузим и валидируем

            $tr = Yii::$app->db->beginTransaction(); //транзакция так как испоьзуем несколько моделей
            /** @var Dialogs $obDialog */
            $obDialog = new Dialogs();  //новый диалог
            $obDialog->buser_id = $iUserID; //кто создал
            $obDialog->status = Dialogs::PUBLISHED; //публикуем диалог
            $obDialog->theme = Yii::t('app/crm','User {user} create new task',[ //тема диалога
                'user'=>Yii::$app->user->identity->getFio()
            ]).' "'.$model->title.'"';

            $arBUIDs = [$iUserID,$model->assigned_id]; //пользователя для которых добавляется диалог

            if(!empty($model->cmp_id))  //если выбрана компания, то привяжем диалог к компания
                $obDialog->crm_cmp_id = $model->cmp_id;

            $obContact = NULL;
            if(!empty($model->contact_id))  //если выбран контакт, то привяжем диалог к контакту
            {
                /** @var CrmCmpContacts $obContact */
                $obContact = CrmCmpContacts::find()
                    ->select(['cmp_id'])
                    ->where(['id' => $model->contact_id])
                    ->one();   //находим контакт
                if($obContact && !empty($obContact->cmp_id))    //нашли контак, проверим не привязан ли контакт к компании
                {
                    $obDialog->crm_cmp_id = $obContact->cmp_id; //привяжем диалог к компании контакта
                }
                $obDialog->crm_cmp_contact_id = $model->contact_id; //привяжем диалог к контакту
            }

            if($obDialog->save()) //сохраняем диалог
            {
                $model->dialog_id = $obDialog->id;
                if($model->save()) //сохраняем задачу
                {

                    if(!empty($obDialog->crm_cmp_id))   //ищем пользователй для компании
                        $arBUIDs = ArrayHelper::merge(
                            $arBUIDs,
                            CUserCrmRulesManager::getBuserIdsByPermission(
                                $obDialog->crm_cmp_id,
                                $iUserID
                            )
                        );

                    if(!empty($obDialog->crm_cmp_contact_id))   //ищем пользователй для контакта
                        $arBUIDs = ArrayHelper::merge(
                            $arBUIDs,
                            CUserCrmRulesManager::getBuserByPermissionsContact(
                                $obDialog->crm_cmp_contact_id,
                                $iUserID,$obContact
                            )
                        );

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
                        Yii::$app->session->addFlash('success',Yii::t('app/crm','Task successfully added'));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            }
            $tr->rollBack();
            Yii::$app->session->setFlash('error',Yii::t('app/crm','Error. Can not add new task'));
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
            return $this->render('create', [
                'model' => $model,
                'sAssName' => $sAssName,
                'cuserDesc' => $cuserDesc,
                'contactDesc' => $contactDesc
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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
                'sAssName' => $sAssName
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

    public function actionOpenTask()
    {
        $data = $this->changeStatus(CrmTask::STATUS_OPENED);
        return $this->returnJsonHelper($data);
    }

    /**
     *
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
                'content' => $this->renderPartial('part/_woked_time_area',['obLog' => $obTimeArr]),
                'timeSpend' => \common\components\helpers\CustomHelper::getFormatedTaskTime($timeSpend)
            ];
        }

        return ['error' => $obLog->getErrors(),'model' => NULL,'content' => NULL,'timeSpend' => NULL];
    }

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
        $obFile = CrmCmpFile::findOne(['id' => $id]);
        if(!$obFile)
            throw new NotFoundHttpException('File not found');
        return Yii::$app->response->sendFile($obFile->getFilePath());
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
}
