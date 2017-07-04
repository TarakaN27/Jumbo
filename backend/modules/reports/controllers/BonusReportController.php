<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.3.16
 * Time: 10.49
 */

namespace backend\modules\reports\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\reports\forms\BonusReportsForm;
use yii\filters\AccessControl;
use Yii;
class BonusReportController extends AbstractBaseBackendController
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


	public function actionIndex()
	{
	    if(Yii::$app->user->id == 12)
	        die;
		$model = new BonusReportsForm();
		if(!\Yii::$app->user->can('adminRights'))
			$model->users = [\Yii::$app->user->id];

		$data = [];
		if($model->load(\Yii::$app->request->post()))
		{
			$data = $model->makeRequest();
		}
		return $this->render('index',[
			'model' => $model,
			'data' => $data
		]);
	}
}