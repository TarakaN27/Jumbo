<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.4.16
 * Time: 12.16
 */

namespace console\controllers;


use common\components\crunchs\Payment\RecalcQuantityHours;
use console\components\AbstractConsoleController;
use yii\console\Controller;

class RecalculateController extends AbstractConsoleController
{
    /**
     * @return int
     */
    public function actionQuantityHours()
    {
        $obQantity = new RecalcQuantityHours();
        $obQantity->run();
        return Controller::EXIT_CODE_NORMAL;
    }
}