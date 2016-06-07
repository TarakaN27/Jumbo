<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 8.12.15
 * Time: 14.38
 */

namespace app\modules\crm\controllers;


use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use backend\widgets\Alert;
use common\components\notification\RedisNotification;
use common\models\AbstractActiveRecord;
use common\models\BUserCrmRules;
use common\models\CrmCmpContacts;
use common\models\CrmCmpFile;
use common\models\CrmTask;
use common\models\CrmTaskRepeat;
use common\models\CUser;
use common\models\CUserGroups;
use common\models\CuserServiceContract;
use common\models\search\CrmTaskSearch;
use common\models\search\CUserSearch;
use common\models\Services;
use Yii;
use common\models\CUserRequisites;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\filters\AccessControl;
class CompanyController extends AbstractBaseBackendController
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
					'actions' => ['delete'],
					'roles' => ['admin']
				],
				[
					'allow' => true,
					'actions' => ['index','view-requisites','view'],
					'roles' => ['user']
				],
				[
					'allow' => true,
					'roles' => ['e_marketer','jurist','moder','bookkeeper','admin']
				]
			]
		];
		return $tmp;
	}

	public function actionIndex()
	{
		//получаем уровень доступа на чтение компаний
		$iAccessLevel = \Yii::$app->user->getCRMLevelAccess(CUser::getModelName(),BUserCrmRules::READ_ACTION);
		$dataProvider = NULL;

		$searchModel = new CUserSearch();
		switch($iAccessLevel)
		{
			case BUserCrmRules::RULE_ALL: //видны все
				$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
				break;

			case BUserCrmRules::RULE_THEMSELF: //только свои. Ответственный и создал компанию
				$dataProvider = $searchModel->search(
					Yii::$app->request->queryParams,
					'('.CUser::tableName().'.manager_id = :userID OR '.CUser::tableName().'.created_by = :userID  OR '.CUser::tableName().'.manager_crc_id = :userID )' ,
					[
						':userID' => Yii::$app->user->id
					]
				);
				break;

			case BUserCrmRules::RULE_OPENED: //только открытые
				$dataProvider = $searchModel->search(
					Yii::$app->request->queryParams,
					['is_opened' => CUser::IS_OPENED]
				);
				break;

			default:
				$dataProvider = $searchModel->search(
					Yii::$app->request->queryParams,
					'1=0'
				);
				break;
		}

		$arCompanyRedisList = RedisNotification::getCompanyListForUser(Yii::$app->user->id);
		$manCrcValue = '';
		if(!empty($searchModel->manager_crc_id))
			$manCrcValue = BUser::findOneByIdCachedForSelect2($searchModel->manager_crc_id);

		$manValue = '';
		if(!empty($searchModel->manager_id))
			$manValue = BUser::findOneByIdCachedForSelect2($searchModel->manager_id);

		return $this->render('index',[
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel,
			'arCompanyRedisList' => $arCompanyRedisList,
			'manCrcValue' => $manCrcValue,
			'manValue' => $manValue
		]);
	}

	/**
	 * @return string
	 * @throws \yii\db\Exception
	 */
	public function actionCreate($is_partner = FALSE)
	{
		$model = new CUser();
		$model->setDummyFields(); //@todo утановлены заглушки на имя пользователя и емаил. При необходимости убрать!
		$model->manager_id = Yii::$app->user->id;
		$model->created_by = Yii::$app->user->id;
		$modelR = new CUserRequisites();

		if($is_partner)
		{
			$model->partner_manager_id = Yii::$app->user->id;
			$model->partner = AbstractActiveRecord::YES;
		}

		if ($model->load(Yii::$app->request->post()) && $modelR->load(Yii::$app->request->post())) {

			if($model->is_resident != CUser::RESIDENT_YES)
				$modelR->isResident = FALSE;

			$modelR->contructor = $model->contractor;
			$modelR->allow_expense = $model->allow_expense;
			if($model->validate() && $modelR->validate())
			{
				$transaction = Yii::$app->db->beginTransaction(); //транзакция для того чтобы при ошибках сохранения не создавалось лишних записей
				try{
					if($modelR->save() && $model->save())
					{
						$model->link('requisites',$modelR);
						$transaction->commit();
						$model->callSaveDoneEvent();
						Yii::$app->session->set('success',Yii::t('app/users','Contractor_successfully_added'));
						return $this->redirect(['view', 'id' => $model->id]);
					}else{
						$transaction->rollBack();
					}
				}catch (\Exception $e)
				{
					$transaction->rollBack();
					Yii::$app->session->set('error',$e->getMessage());
				}
			}else{
				Yii::$app->session->set('error',Yii::t('app/users','Contractor_validate_error'));
			}
		}

		if(empty($modelR->type_id))
			$modelR->type_id = CUserRequisites::TYPE_F_PERSON;

		return $this->render('create', [
			'model' => $model,
			'modelR' => $modelR
		]);
	}

	/**
	 * @param $id
	 * @return string|\yii\web\Response
	 */
	public function actionView($id)
	{
		$iUserID = Yii::$app->user->id; //текущий пользователь

		//Все данные по задаче
		/** @var CUser $model */
		$model = CUser::findOneByIDCached($id);

		if(!$model)
			throw new NotFoundHttpException('Company not found');

		$model->callViewedEvent();  //событие просмотрено

		/** @var CUserRequisites $obRequisite */
		$obRequisite = $model->requisites;
		$arContacts =  $model->crmContacts;
		$arFile = $model->crmFiles;
		$obQHour = $model->quantityHour;

		//Модель для контактов
		$obModelContact = new CrmCmpContacts();
		$obModelContact->cmp_id = $id;
		$obModelContact->type = CrmCmpContacts::TYPE_CLIENT;
		$obModelContact->assigned_at =$iUserID;
		$obModelContact->is_opened = $model->is_opened; //по умолчанию для контакта ставим уровень такой же как и у компании
		$obModelContact->created_by = $iUserID;

		//Модель для файла
		$obFile = new CrmCmpFile();
		$obFile->setScenario('insert');
		$obFile->cmp_id = $id;


		//Модель для задач
		$modelTask = new CrmTask();
		//дефолтные состояния
		$modelTask->created_by = $iUserID;  //кто создал задачу
		$modelTask->assigned_id = $iUserID; //по умолчанию вешаем сами на себя
		$modelTask->status = CrmTask::STATUS_OPENED; //статус. По умолчанию открыта
		$modelTask->cmp_id = $id;   //вешаем компанию
		$modelTask->task_control = CrmTask::YES;    //принять после выполнения по-умолчанию
		$modelTask->repeat_task = CrmTask::NO;
		$data = [];

		$obTaskRepeat = new CrmTaskRepeat();    //task repeat parameters
		$obTaskRepeat->initForCreate();

		//получаем группы к которой принадлежит компания
		$arGroups = CUserGroups::find()
			->alias('gr')
			->joinWith('cuserObjects cu')
			->joinWith('cuserObjects.requisites')
			->where([
				'cu.id' => $id
			])
			->all();

		/**
		 * Добавление задачи
		 */
		if($modelTask->load(Yii::$app->request->post()) && $modelTask->validate())
		{
			$validRepeat = TRUE;
			if ($modelTask->repeat_task) {
				if (!$obTaskRepeat->load(Yii::$app->request->post()) || ($obTaskRepeat->load(Yii::$app->request->post()) && !$obTaskRepeat->validate())) {
					$validRepeat = FALSE;
				}
			}

			if($validRepeat && $modelTask->createTask($iUserID))
			{
				Yii::$app->session->addFlash('success',Yii::t('app/crm','Task successfully added'));
				return $this->redirect(['view', 'id' => $id,'#' => 'tab_content2']);
			}else{
				Yii::$app->session->setFlash('error',Yii::t('app/crm','Error. Can not add new task'));
				return $this->redirect(['view','id' => $id]);
			}
		}

		/**
		 * Добавление контакта
		 */
		if($obModelContact->load(Yii::$app->request->post()) )
		{
			if($obModelContact->save())
			{
				Yii::$app->session->setFlash('success',Yii::t('app/crm','Contact successfully added'));
				return $this->redirect(['view','id' => $id]);
			}else{
				Yii::$app->session->setFlash('error',Yii::t('app/crm','Error. Can not add contact'));
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

		/**
		 * Смена отвественного
		 */
		if($model->load(Yii::$app->request->post()))
		{
            $model->setScenario(CUser::SCENARIO_CHANGE_ASSIGNE);
			if($model->save())
			{
				Yii::$app->session->setFlash('success',Yii::t('app/crm','Assigned successfully changed'));
				return $this->redirect(['view','id' => $id]);
			}else{
				Yii::$app->session->setFlash('error',Yii::t('app/crm','Assigned changed with error'));
			}
		}

		$obCrmTaskSearch = new CrmTaskSearch();
		$dataProviderTask = $obCrmTaskSearch->search(
			Yii::$app->request->queryParams,
			CrmTaskSearch::VIEW_TYPE_FULL_TASK_AND_OWNER,
			['cmp_id' => $model->id],
			[],
			FALSE,
			25
			);

		$sAssName = BUser::findOne($modelTask->assigned_id)->getFio();

		if(!empty($modelTask->contact_id))
			$contactDesc = \common\models\CrmCmpContacts::findOne($modelTask->contact_id)->fio;
		else
			$contactDesc = '';
		$dataContact = [];
		foreach($arContacts as $obCnt)
			$dataContact[$obCnt->id] = $obCnt->fio;

		return $this->render('view',[
			'model' => $model,
			'obRequisite' => $obRequisite,
			'arContacts' => $arContacts,
			'obModelContact' => $obModelContact,
			'obFile' => $obFile,
			'arFile' => $arFile,
			'dataProviderTask' => $dataProviderTask,
			'modelTask' => $modelTask,
			'contactDesc' => $contactDesc,
			'dataContact' => $dataContact,
			'sAssName' => $sAssName,
			'data' => $data,
			'obQHour' => $obQHour,
			'arGroups' => $arGroups,
			'obTaskRepeat' => $obTaskRepeat
		]);
	}

	/**
	 * @param $cmpID
	 * @param $id
	 * @return $this
	 * @throws NotFoundHttpException
	 */
	public function actionDownloadFile($id)
	{
		$obFile = CrmCmpFile::findOne(['id' => $id]);
		if(!$obFile)
			throw new NotFoundHttpException('File not found');
		return Yii::$app->response->sendFile($obFile->getFilePath(),$obFile->name.'.'.$obFile->ext);
	}

	/**
	 * @throws ServerErrorHttpException
	 * @throws \yii\base\ExitException
	 */
	public function actionEditContacts()
	{
		$pk = Yii::$app->request->post('pk');
		$name = Yii::$app->request->post('name');
		$value = Yii::$app->request->post('value');
		try {
			$obContact = CrmCmpContacts::findOne($pk);
			if (!$obContact)
				throw new NotFoundHttpException('Contact not found');

			$obContact->$name = $value;
			if (!$obContact->save())
				throw new ServerErrorHttpException();
		}catch (Exception $e)
		{
			throw new ServerErrorHttpException();
		}
		Yii::$app->end(200);
	}

	/**
	 * @param $id
	 * @return Response
	 * @throws NotFoundHttpException
	 */
	public function actionArchive($id)
	{
		/** @var  CUser $model */
		$model = CUser::findOne($id);
		if(!$model)
			throw new NotFoundHttpException();

		$model->archive = $model->archive == CUser::ARCHIVE_YES ? CUser::ARCHIVE_NO : CUser::ARCHIVE_YES;
		if($model->save()) {
			$str = $model->archive == CUser::ARCHIVE_YES ?
				Yii::t('app/crm', 'Company moved to the archive') :
				Yii::t('app/crm', 'Company comeback from archive');
			Yii::$app->session->setFlash(Alert::TYPE_SUCCESS, $str);
		}
		else
			Yii::$app->session->setFlash(Alert::TYPE_ERROR,Yii::t('app/crm','Error. Can not set archive status for company'));

		return $this->redirect(Url::to(['index']));
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
	 * @param $id
	 * @return Response
	 * @throws NotFoundHttpException
	 * @throws \Exception
	 */
	public function actionDelete($id)
	{
		/** @var  CUser $model */
		$model = CUser::findOne($id);
		if(!$model)
			throw new NotFoundHttpException();
		$cmpName = $model->getInfo();
		if($model->delete())
			Yii::$app->session->setFlash(Alert::TYPE_SUCCESS,Yii::t('app/crm','Company {company} successfully deleted',[
				'company' => $cmpName
			]));
		else
			Yii::$app->session->setFlash(Alert::TYPE_ERROR,Yii::t('app/crm','Error can not delete company'));

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 * @return string
	 * @throws NotFoundHttpException
	 * @throws \yii\db\Exception
	 */
	public function actionUpdate($id)
	{
		/** @var  CUser $model */
		$model = CUser::findOne($id);
		if(!$model)
			throw new NotFoundHttpException();
		$modelR = $model->requisites;

		if($model->partner)
			$model->partner_archive_date = Yii::$app->formatter->asDate('NOW');

		if(empty($modelR))
			$modelR = new CUserRequisites();

		if ($model->load(Yii::$app->request->post()) && $modelR->load(Yii::$app->request->post())) {

			if($model->is_resident != CUser::RESIDENT_YES)
				$modelR->isResident = FALSE;

			$modelR->contructor = $model->contractor;

			if($model->validate() && $modelR->validate())
			{
				$transaction = Yii::$app->db->beginTransaction(); //транзакция для того чтобы при ошибках сохранения не создавалось лишних записей
				try{
					if($modelR->save() && $model->save())
					{
						$model->link('requisites',$modelR);
						$transaction->commit();
						Yii::$app->session->set('success',Yii::t('app/users','Contractor_successfully_updated'));
						return $this->redirect(['view', 'id' => $model->id]);
					}else{
						$transaction->rollBack();
					}
				}catch (\Exception $e)
				{
					$transaction->rollBack();
					Yii::$app->session->set('error',$e->getMessage());
					return $this->redirect(['update', 'id' => $model->id]);
				}
			}else{
				Yii::$app->session->set('error',Yii::t('app/users','Contractor_validate_error'));
			}
		}

		if(empty($modelR->type_id))
			$modelR->type_id = CUserRequisites::TYPE_F_PERSON;

		return $this->render('update', [
			'model' => $model,
			'modelR' => $modelR
		]);

	}

	/**
	 * @param $id
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionViewRequisites($id)
	{
		/** @var CUser $model */
		$model = CUser::find()->where(['id' => $id])->with('prospects')->one();
		if(!$model)
			throw new NotFoundHttpException('Company not found');
		$obReq = $model->requisites;
		$arServices  = Services::getServicesMap();
		$arCSCTmp= CuserServiceContract::find()->where(['cuser_id' => $id])->all();

		$arCSC = [];
		foreach($arCSCTmp as $csc)
			$arCSC[$csc->service_id] = $csc;

		return $this->render('view_requisites',[
			'model' => $model,
			'modelR' => $obReq,
			'arServices' => $arServices,
			'arCSC' => $arCSC
		]);
	}

}