<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.2.16
 * Time: 11.59
 */

namespace backend\modules\reports\controllers;

use backend\components\AbstractBaseBackendController;
use common\components\calendar\TimeSheet;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class TimesheetController extends AbstractBaseBackendController
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => ['get-timesheet-users'],
						'allow' => true,
						'roles' => ['admin']
					],
					[
						'actions' => ['index','get-time-sheet'],
						'allow' => true,
						'roles' => ['user'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'get-time-sheet' => ['post'],
				],
			],
		];
	}

	/**
	 * @return string
	 */
	public function actionIndex()
	{
		$viewTemplate = \Yii::$app->user->can('adminRights') ? 'index_admin' : 'index';
		return $this->render($viewTemplate,[]);
	}

	/**
	 * @return array
	 * @throws ForbiddenHttpException
	 */
	public function actionGetTimeSheet()
	{
		$userID = \Yii::$app->request->post('user');
		$startDate = \Yii::$app->request->post('startDate');
		$endDate  = \Yii::$app->request->post('endDate');

		if(!\Yii::$app->user->can('adminRights') && $userID != \Yii::$app->user->id)
			throw new ForbiddenHttpException('You are not allowed do this action');

		$obTimesheet = new TimeSheet($userID,$startDate,$endDate);

		$data = $obTimesheet->getTimesheetData();

		\Yii::$app->response->format = Response::FORMAT_JSON;

		return [
			'content' => $this->renderAjax('get-time-sheet',[
				'data' => $data
			])
		];
	}

	/**
	 * @return array
	 * @throws ForbiddenHttpException
	 */
	public function actionGetTimesheetUsers()
	{
		$startDate = \Yii::$app->request->post('startDate');
		$endDate  = \Yii::$app->request->post('endDate');

		$obTimesheet = new TimeSheet(\Yii::$app->user->id,$startDate,$endDate);

		$data = $obTimesheet->getUsersTimeSheetData();

		\Yii::$app->response->format = Response::FORMAT_JSON;
		return [
			'content' => $this->renderAjax('get-users-time-sheet',[
				'data' => $data
			])
		];

	}

}