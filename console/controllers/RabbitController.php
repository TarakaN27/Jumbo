<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 28.6.16
 * Time: 12.27
 */

namespace console\controllers;


use console\components\AbstractConsoleController;
use common\components\rabbitmq\Rabbit;
use common\components\rabbitmq\workers\ActsLetterRabbitHandler;

class RabbitController extends AbstractConsoleController
{
    public function actionIndex()
    {

    }

    /**
     * @return int
     */
    public function actionActsLetter()
    {
        $handler = new ActsLetterRabbitHandler();
        \Yii::$app->rabbit->listener(Rabbit::QUEUE_ACTS_SEND_LETTER,[$handler,'processing']);
        return $this->log(TRUE);
    }
}