<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.4.16
 * Time: 18.03
 * Контроллер должен запускаться каждое 1 число месяца в 3.00 
 */

namespace console\controllers;


use common\components\bonus\BonusRecordCalculate;
use common\components\helpers\CustomHelper;
use common\components\partners\PartnerPercentCounting;
use console\components\AbstractConsoleController;
use yii\base\Exception;
use yii\helpers\Console;
use common\models\Payments;

class MonthlyController extends AbstractConsoleController
{
    /**
     *
     */
    public function actionRun()
    {
   /*     try {
            $obPartnerPercent = new PartnerPercentCounting();   //считаем проценты для партнеров
            $obPartnerPercent->countPercentByMonth();
        }catch (Exception $e)
        {
            //@todo add cron error notification
        }
   */

            $time = CustomHelper::getDateMinusNumMonth(time(),1);   //считаем бонусы по рекордам платежей аккаунтеров
            $obRecordCalculate = new BonusRecordCalculate(date('d.m.Y',$time));
            $obRecordCalculate->run();
        return self::EXIT_CODE_NORMAL;
    }

}