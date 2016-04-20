<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.4.16
 * Time: 18.03
 * Контроллер должен запускаться каждое 1 число месяца в 3.00 
 */

namespace console\controllers;


use common\components\partners\PartnerPercentCounting;
use console\components\AbstractConsoleController;
use yii\helpers\Console;

class MonthlyController extends AbstractConsoleController
{
    /**
     *
     */
    public function actionRun()
    {
        $obPartnerPercent = new PartnerPercentCounting();   //считаем проценты для партнеров
        $obPartnerPercent->countPercentByMonth();
        return self::EXIT_CODE_NORMAL;
    }

}