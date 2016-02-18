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
use common\models\CrmCmpContacts;
use common\models\CUser;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\Query;
use common\models\CUserRequisites;

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
						'add-comment' => ['post'],
						'add-message' => ['post'],
						'load-dialog' => ['post'],
						'add-new-message' => ['post'],
						'add-dialog' => ['post'],
						'add-new-dialog' => ['post']
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
}