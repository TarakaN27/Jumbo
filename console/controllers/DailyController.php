<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.4.16
 * Time: 18.07
 * Контроллер запускается кадый день в 3.00
 */

namespace console\controllers;


use common\components\partners\PartnerInactivity;
use console\components\AbstractConsoleController;

class DailyController extends AbstractConsoleController
{
    public function actionRun()
    {
        $obPartnerInactiovity = new PartnerInactivity();
        $obPartnerInactiovity->checkStartPeriod();
        $obPartnerInactiovity->clearProperty();
        $obPartnerInactiovity->checkRegularPeriod();
        return self::EXIT_CODE_NORMAL;
    }
}