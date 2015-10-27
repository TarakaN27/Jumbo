<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 20.10.15
 * Time: 15.04
 */
namespace backend\modules\users\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\CuserSettings;
use Yii;

class ContractorSettingsController extends AbstractBaseBackendController
{
	/**
	 * @param $userID
	 * @return string|\yii\web\Response
	 */
	public function actionIndex($userID)
	{
		$model = CuserSettings::findOne(['cuser_id' => $userID]);
		if(!$model) {
			$model = new CuserSettings();
			$model->cuser_id = $userID;
		}

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index', 'userID' => $userID]);
		}

		return $this->render('index', [
				'model' => $model,
			]);
	}

}