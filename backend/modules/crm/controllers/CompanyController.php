<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 8.12.15
 * Time: 14.38
 */

namespace app\modules\crm\controllers;


use backend\components\AbstractBaseBackendController;
use common\models\BUserCrmRules;
use common\models\CrmCmpContacts;
use common\models\CrmCmpFile;
use common\models\CUser;
use common\models\search\CUserSearch;
use Yii;
use common\models\CUserRequisites;
use yii\web\NotFoundHttpException;

class CompanyController extends AbstractBaseBackendController
{

	public function actionIndex()
	{
		//получаем уровень доступа на чтение компаний
		$iAccessLevel = \Yii::$app->user->getCRMLevelAccess(CUser::getModelName(),BUserCrmRules::READ_ACTION);
		$dataProvider = NULL;
		$searchModel = NULL;
		switch($iAccessLevel)
		{
			case BUserCrmRules::RULE_ALL: //видны все
				$searchModel = new CUserSearch();
				$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
				break;

			case BUserCrmRules::RULE_THEMSELF: //только свои. Ответственный и создал компанию
				$searchModel = new CUserSearch();
				$dataProvider = $searchModel->search(
					Yii::$app->request->queryParams,
					'('.CUser::tableName().'.manager_id == :userID AND '.CUser::tableName().'.created_by == :userID' ,
					[
						':userID' => Yii::$app->user->id
					]
				);
				break;

			case BUserCrmRules::RULE_OPENED: //только открытые
				$searchModel = new CUserSearch();
				$dataProvider = $searchModel->search(
					Yii::$app->request->queryParams,
					['is_opened' => CUser::IS_OPENED]
				);
				break;

			default:
				break;
		}

		return $this->render('index',[
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel
		]);
	}

	public function actionCreate()
	{
		$model = new CUser();
		$model->setDummyFields(); //@todo утановлены заглушки на имя пользователя и емаил. При необходимости убрать!
		$modelR = new CUserRequisites();
		if ($model->load(Yii::$app->request->post()) && $modelR->load(Yii::$app->request->post())) {

			if($model->is_resident != CUser::RESIDENT_YES)
				$modelR->isResident = FALSE;

			if($model->validate() && $modelR->validate())
			{
				$transaction = Yii::$app->db->beginTransaction(); //транзакция для того чтобы при ошибках сохранения не создавалось лишних записей
				try{
					if($modelR->save() && $model->save())
					{
						$model->link('requisites',$modelR);
						$transaction->commit();
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

	public function actionView($id)
	{
		$model = CUser::findOneByIDCached($id);
		$obRequisite = $model->requisites;
		$arContacts =  $model->crmContacts;
		$arFile = $model->crmFiles;
		$obModelContact = new CrmCmpContacts();
		$obModelContact->cmp_id = $id;
		$obModelContact->type = CrmCmpContacts::TYPE_CLIENT;
		$obModelContact->assigned_at = Yii::$app->user->id;
		$obFile = new CrmCmpFile();
		$obFile->setScenario('insert');
		$obFile->cmp_id = $id;

		/**
		 * добавление контакта
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

		return $this->render('view',[
			'model' => $model,
			'obRequisite' => $obRequisite,
			'arContacts' => $arContacts,
			'obModelContact' => $obModelContact,
			'obFile' => $obFile,
			'arFile' => $arFile
		]);
	}

	/**
	 * @param $cmpID
	 * @param $id
	 * @return $this
	 * @throws NotFoundHttpException
	 */
	public function actionDownloadFile($cmpID,$id)
	{
		$obFile = CrmCmpFile::findOne(['id' => $id,'cmp_id' => $cmpID]);
		if(!$obFile)
			throw new NotFoundHttpException('File not found');
		return Yii::$app->response->sendFile($obFile->getFilePath());
	}

}