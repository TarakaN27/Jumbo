<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.2.16
 * Time: 11.59
 */

namespace backend\modules\users\controllers;


use common\models\CUser;
use common\models\CuserQuantityHour;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

class QuantityHoursController extends Controller
{
	public function actionIndex($iCID)
	{
		/** @var CUser $obUser */
		$obUser = CUser::find()->where(['id' => $iCID])->one();
		if(!$obUser)
			throw new NotFoundHttpException('Not found');
		if(!\Yii::$app->user->can('adminRights') && \Yii::$app->user->id != $obUser->manager_id)
		{
			throw new NotAcceptableHttpException();
		}

		$obQHour = $obUser->quantityHour;
		if(!$obQHour)
		{
			$obQHour = new CuserQuantityHour();
			$obQHour->cuser_id = $iCID;
			$obQHour->hours = 0;
			$obQHour->spent_time = 0;
		}

		if($obQHour->load(\Yii::$app->request->post()))
		{
			if($obQHour->save())
			{
				\Yii::$app->session->setFlash('success',\Yii::t('app/users','Successfully saved'));
			}else{
				\Yii::$app->session->setFlash('error',\Yii::t('app/users','Error'));
			}

			return $this->redirect(Url::current());
		}

		return $this->render('index',[
			'obQHour' => $obQHour
		]);
	}

}