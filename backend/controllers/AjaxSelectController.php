<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.12.15
 * Time: 11.44
 */

namespace backend\controllers;


use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\components\helpers\CustomHelper;
use common\models\AbstractActiveRecord;
use common\models\CrmCmpContacts;
use common\models\CrmTask;
use common\models\CUser;
use common\models\ExchangeCurrencyHistory;
use common\models\PaymentCondition;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\db\Query;
use common\models\CUserRequisites;
use Yii;
class AjaxSelectController extends AbstractBaseBackendController
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
					'roles' => ['@'],
				],
				'verbs' => [
					'class' => VerbFilter::className(),
					'actions' => [
						'get-cmp' => ['post'],
						'get-contractor' => ['post'],
						'get-b-user' => ['post'],
						'get-crm-contact' => ['post'],
						'get-parent-crm-task' => ['post'],
						'get-partners' => ['post']
					],
				],
			]
		];
		return $tmp;
	}
	/**
	 * Контроллер по умолчанию всегда возвращает json!!!!
	 */
	public function init()
	{
		\Yii::$app->response->format = Response::FORMAT_JSON;
		return parent::init();
	}

	/**
	 * @param null $q
	 * @param null $id
	 * @return array
	 */
	public function actionGetCmp($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {

			$obCUser = CUser::find()
				->select([CUser::tableName().'.id','requisites_id'])
				->joinWith('requisites')

				->where(['like',CUserRequisites::tableName().'.corp_name',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_lname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_fname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_mname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.site',$q])
				->notArchive()
				->limit(10)
				->all()
			;

			foreach($obCUser as $user)
				$out['results'] []= [
					'id' => $user->id,
					'text' => $user->getInfoWithSite()
				];
			$out['results'] = array_values($out['results']);

		}
		elseif ($id > 0) {
			$out['results'] = ['id' => $id, 'text' => CUser::findOne($id)->getInfoWithSite()];
		}
		return $out;
	}

	/**
	 * @param null $q
	 * @param null $id
	 * @return array
	 */
	public function actionGetCmpForUser($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {

			$obCUser = CUser::find()
				->select([CUser::tableName().'.id','requisites_id'])
				->joinWith('requisites')

				->where(['like',CUserRequisites::tableName().'.corp_name',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_lname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_fname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_mname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.site',$q])
				->notArchive();

			if(!Yii::$app->user->can('adminRights'))
				$obCUser = $obCUser->andWhere([CUser::tableName().'.manager_id' => Yii::$app->user->id]);

			$obCUser = $obCUser->limit(10)

			//	->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;

			//echo $obCUser;die;

				->all()
			;

			foreach($obCUser as $user)
				$out['results'] []= [
					'id' => $user->id,
					'text' => $user->getInfoWithSite()
				];
			$out['results'] = array_values($out['results']);

		}
		elseif ($id > 0) {
			$out['results'] = ['id' => $id, 'text' => CUser::findOne($id)->getInfoWithSite()];
		}
		return $out;
	}

	public function actionGetContractor($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {

			$obCUser = CUser::find()
				->select([CUser::tableName().'.id','requisites_id'])
				->joinWith('requisites')
				->where(['like',CUserRequisites::tableName().'.corp_name',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_lname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_fname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_mname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.site',$q])
				->andWhere(['contractor' => CUser::CONTRACTOR_YES])
				->notArchive()
				->limit(10)
				->all()
			;

			foreach($obCUser as $user)
				$out['results'] []= [
					'id' => $user->id,
					'text' => $user->getInfoWithSite()
				];
			$out['results'] = array_values($out['results']);

		}
		elseif ($id > 0) {
			$out['results'] = ['id' => $id, 'text' => CUser::find()->where(['contractor' => CUser::CONTRACTOR_YES,'id' => $id])->one()->getInfoWithSite()];
		}
		return $out;
	}

	/**
	 * @param null $q
	 * @param null $id
	 * @return array
	 */
	public function actionGetExpenseUser($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {

			$obCUser = CUser::find()
				->select([
					CUser::tableName().'.id',
					'requisites_id',
					CUserRequisites::tableName().'.corp_name',
					CUserRequisites::tableName().'.j_lname',
					CUserRequisites::tableName().'.j_fname',
					CUserRequisites::tableName().'.j_mname',
					CUserRequisites::tableName().'.site'
				])
				->joinWith('requisites')
				->where(['like',CUserRequisites::tableName().'.corp_name',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_lname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_fname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_mname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.site',$q])
				->andWhere(['allow_expense' => CUser::CONTRACTOR_YES])
				->notArchive()
				->limit(10)
				->all()
			;

			foreach($obCUser as $user)
				$out['results'] []= [
					'id' => $user->id,
					'text' => $user->getInfoWithSite()
				];
			$out['results'] = array_values($out['results']);

		}
		elseif ($id > 0) {
			$out['results'] = ['id' => $id, 'text' => CUser::find()->where(['allow_expense' => CUser::CONTRACTOR_YES,'id' => $id])->one()->getInfoWithSite()];
		}
		return $out;
	}

	/**
	 * @param null $q
	 * @param null $id
	 * @return array
	 */
	public function actionGetBUser($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {

			$obBUser = BUser::find()
				->select(['id','fname','lname','mname','username'])
				->where(['like','username',$q])
				->orWhere(['like','mname',$q])
				->orWhere(['like','lname',$q])
				->orWhere(['like','fname',$q])
				->limit(10)
				->all()
			;

			foreach($obBUser as $user)
				$out['results'] []= [
					'id' => $user->id,
					'text' => $user->getFio()
				];
			$out['results'] = array_values($out['results']);

		}
		elseif ($id > 0) {
			$out['results'] = ['id' => $id, 'text' => BUser::findOne($id)->getFio()];
		}
		return $out;
	}

	/**
	 * @param null $q
	 * @param null $id
	 * @return array
	 */
	public function actionGetCrmContact($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {

			$obBUser = CrmCmpContacts::find()
				->select(['id','fio'])
				->where(['like','fio',$q])
				->limit(10)
				->all()
			;
			foreach($obBUser as $user)
				$out['results'] []= [
					'id' => $user->id,
					'text' => $user->fio
				];
			$out['results'] = array_values($out['results']);

		}
		elseif ($id > 0) {
			$out['results'] = ['id' => $id, 'text' => CrmCmpContacts::findOne($id)->fio];
		}
		return $out;
	}

	/**
	 * @param null $q
	 * @param null $id
	 * @return array
	 */
	public function actionGetParentCrmTask($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if(!is_null($q))
		{
			$query = CrmTask::find()    //находим родительские задачи
				->select(['id','title'])
				->where('parent_id is null OR parent_id = 0');

			if(!Yii::$app->user->can('adminRights'))    //если поьзователь не админ, то выдаем только свои таски
				$query->andWhere(['created_by' => Yii::$app->user->id]);

			$query->andWhere(' title LIKE :q OR id = :id');
			$query->params([
				':q' => '%'.$q.'%',
				':id' => $q
			]);

			$query->limit(10);

			$arTask = $query->all();

			foreach($arTask as $task)
			{
				$out['results'] [] = [
					'id' => $task->id,
					'text' => $task->id.' - '.CustomHelper::cuttingString($task->title,100)
				];
			}

			$out['results'] = array_values($out['results']);
		}elseif($id > 0)
		{
			$out['results'] = ['id' => $id, 'text' => $id.' - '.CustomHelper::cuttingString(CrmTask::findOne($id)->title,100)];
		}

		return $out;
	}

	/**
	 * @param null $q
	 * @param null $id
	 * @return array
	 */
	public function actionGetPartners($q = null, $id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($q)) {

			$obCUser = CUser::find()
				->select([
					CUser::tableName().'.id',
					CUser::tableName().'.requisites_id',
					CUser::tableName().'.partner',
					CUserRequisites::tableName().'.type_id as req_type',
					CUserRequisites::tableName().'.corp_name',
					CUserRequisites::tableName().'.j_fname',
					CUserRequisites::tableName().'.j_mname',
					CUserRequisites::tableName().'.j_lname',
					CUserRequisites::tableName().'.site',

				])
				->joinWith('requisites')
				->where(['like',CUserRequisites::tableName().'.corp_name',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_lname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_fname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.j_mname',$q])
				->orWhere(['like',CUserRequisites::tableName().'.site',$q])
				->andWhere([CUser::tableName().'.partner' => AbstractActiveRecord::YES])
				->notArchive()
				->limit(10)
				->all()
			;
			/** @var CUser $user */
			foreach($obCUser as $user)
				$out['results'] []= [
					'id' => $user->id,
					'text' => $user->getInfoWithSite()
				];
			$out['results'] = array_values($out['results']);

		}
		elseif ($id > 0) {
			$out['results'] = [
				'id' => $id,
				'text' => is_object($obUser = CUser::find()
					->select([
						CUser::tableName().'.id',
						CUser::tableName().'.requisites_id',
						CUser::tableName().'.partner',
						CUserRequisites::tableName().'.type_id as req_type',
						CUserRequisites::tableName().'.corp_name',
						CUserRequisites::tableName().'.j_fname',
						CUserRequisites::tableName().'.j_mname',
						CUserRequisites::tableName().'.j_lname',
						CUserRequisites::tableName().'.site',

					])
					->joinWith('requisites')
					->where(['contractor' => CUser::CONTRACTOR_YES,'id' => $id])
					->one()) ? $obUser->getInfoWithSite() : NULL
			];
		}
		return $out;
	}

	/**
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function actionGetCondition()
	{
		$iServId = Yii::$app->request->post('iservId');
		$iLegalId = Yii::$app->request->post('iLegalId');
		$amount = Yii::$app->request->post('amount');
		$iCuserId = Yii::$app->request->post('iCuserId');
		$iPayDate = Yii::$app->request->post('iPayDate');
		$iCurrID = Yii::$app->request->post('iCurrID');

		if(empty($iServId) || empty($iLegalId) || empty($amount) || empty($iCuserId) || empty($iPayDate) || empty($iCurrID))
		{
			throw new InvalidParamException('');
		}

		$exhRate = ExchangeCurrencyHistory::getCurrencyInBURForDate($iPayDate,$iCurrID);
		if(!$exhRate)
			throw new NotFoundHttpException('Exchange rate not found');

		$amount = (float)$amount*(float)$exhRate;
		
		$obCuser = CUser::find()->select(['id','is_resident'])->where(['id' => $iCuserId])->one();
		if(!$obCuser)
			throw new NotFoundHttpException('Cuser not found');

		$obConditions = PaymentCondition::getAppropriateConditions($iServId,$iLegalId,$amount,$obCuser->is_resident,$iPayDate);
		if(empty($obConditions))
			return [];

		return PaymentCondition::find()->select(['id','name'])->where(['id'=>array_unique($obConditions)])->all();
	}
}