<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 8.12.15
 * Time: 14.38
 */

namespace app\modules\crm\controllers;


use backend\components\AbstractBaseBackendController;

class CompanyController extends AbstractBaseBackendController
{

	public function actionIndex()
	{

		return $this->render('index',[

		]);
	}
}