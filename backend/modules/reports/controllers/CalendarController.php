<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 9.2.16
 * Time: 10.02
 */

namespace backend\modules\reports\controllers;

use backend\components\AbstractBaseBackendController;
use common\components\calendar\Calendar;
use common\models\CalendarDays;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\filters\AccessControl;

class CalendarController extends AbstractBaseBackendController
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
					'roles' => ['user']
				]
			]
		];
		return $tmp;
	}


	CONST
		YEAR_START = 2016;

	/**
	 * @return int
	 */
	protected function getYears()
	{
		$arReturn = [];
		for($i = self::YEAR_START;$i<=(int)date('Y',time())+1;$i++)
		{
			$arReturn[$i] = $i;
		}
		return $arReturn;
	}

	public function actionIndex($year = NULL)
	{
		if(is_null($year))
			$year = (int)date('Y',time());

		$obCalendar = new Calendar();
		$arDays = $obCalendar->getCalendarForYearArray($year);

		$arYears = $this->getYears();

		return $this->render('index',[
			'arDays' => $arDays,
			'year' => $year,
			'canEdit' =>$this->canEditCalendar(),
			'arYears' => $arYears
		]);
	}

	/**
	 * @return array
	 */
	public function actionEditDate()
	{
		$date = \Yii::$app->request->post('date');
		$type = \Yii::$app->request->post('type');
		$workHour = \Yii::$app->request->post('workHour');
		$year = \Yii::$app->request->post('year');
		$model = CalendarDays::findOne(['date' => $date]);
		if(empty($model)) {
			$model = new CalendarDays();
			$model->buser_id = \Yii::$app->user->id;
			if($type == Calendar::HOLIDAY_DAY)
				$model->type = CalendarDays::TYPE_HOLIDAY;
			else
				$model->type = CalendarDays::TYPE_WORK_DAY;
			$model->work_hour = $workHour;
			$model->date = $date;
		}
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

		return $this->renderAjax('_form_edit_day',[
			'model' => $model,
			'year' =>$year
		]);
	}

	/**
	 * @return array
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 */
	public function actionSave()
	{
		if(!$this->canEditCalendar())
			throw new NotAcceptableHttpException();

		$model = new CalendarDays();
		$year = \Yii::$app->request->post('year');
		$modelID = \Yii::$app->request->post('CalendarDays');
		$modelID = isset($modelID['id']) ? $modelID['id'] : NULL;
		if(!$model->load(\Yii::$app->request->post()))
			throw new ServerErrorHttpException();

		if(!empty($modelID))
		{
			/** @var CalendarDays $tmp */
			$tmp = CalendarDays::findOne($modelID);
			if(empty($tmp))
				throw new NotFoundHttpException();

			$tmp->work_hour = $model->work_hour;
			$tmp->type = $model->type;
			$tmp->description = $model->description;
			$model = $tmp;
		}

		$model->buser_id = \Yii::$app->user->id;

		if(!$model->save())
			throw new ServerErrorHttpException();

		$obCalendar = new Calendar();
		$arDays = $obCalendar->getCalendarForYearArray($year);
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return ['content' => $this->renderPartial('_calendar',['arDays' => $arDays,'year' => $year,'canEdit' =>$this->canEditCalendar()])];
	}

	protected function canEditCalendar()
	{
		return $canEdit = \Yii::$app->user->can('adminRights');
	}

}