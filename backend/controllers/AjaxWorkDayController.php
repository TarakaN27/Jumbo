<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.1.16
 * Time: 12.02
 */

namespace backend\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\WorkDay;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AjaxWorkDayController extends AbstractBaseBackendController
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
						'begin' => ['post'],
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
	 * Начинаем рабочий день
	 * @return array
	 */
	public function actionBegin()
	{
		$model = new WorkDay();
		$model->buser_id = \Yii::$app->user->id;
		if($model->load(\Yii::$app->request->post()))
		{
			$model->end_time = '';
			if($model->save())
			{
				return ['error' => NULL,'model' => $model];
			}
			return ['error' => $model->getErrors(),'model' => NULL];
		}

		return ['error' => 'can not load request','model' => NULL];
	}

	public function actionEnd()
	{
		$id = \Yii::$app->request->post('WorkDay');
		$id = isset($id['id']) ? $id['id'] : NULL;
		$modelTmp = new WorkDay();
		if(!$modelTmp->load(\Yii::$app->request->post()) && $id)
			return ['error' => 'can not load request','model' => NULL];

		/** @var  WorkDay $model */
		$model = WorkDay::findOne($id);
		if(!$model)
			throw new NotFoundHttpException;

		$model->end_time = strtotime($modelTmp->end_time);
		$model->description = $modelTmp->description;

		$model->setSpentTime();
		if($model->save())
		{
			return ['error'  => NULL,'model' => $model];
		}

		return ['error' => $model->getErrors(),'model' => $model];
	}


	public function actionContinue()
	{
		$id = \Yii::$app->request->post('WorkDay');
		$id = isset($id['id']) ? $id['id'] : NULL;
		$modelTmp = new WorkDay();
		if(!$modelTmp->load(\Yii::$app->request->post()) && $id)
			return ['error' => 'can not load request','model' => NULL];

		/** @var  WorkDay $model */
		$model = WorkDay::findOne($id);
		if(!$model)
			throw new NotFoundHttpException;

		$model->begin_time = strtotime($modelTmp->begin_time);
		$model->end_time = '';

		if($model->save())
		{
			return ['error'  => NULL,'model' => $model];
		}

		return ['error' => $model->getErrors(),'model' => $model];
	}
}