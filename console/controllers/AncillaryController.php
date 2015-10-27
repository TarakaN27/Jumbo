<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 26.10.15
 * Time: 16.26
 */

namespace console\controllers;


use console\components\AbstractConsoleController;
use console\components\controllerHelper\ManagerMsg;

class AncillaryController extends AbstractConsoleController
{

	public function run()
	{
		ManagerMsg::checkForOverduePromisedPayment();
		return self::EXIT_CODE_NORMAL;
	}



}