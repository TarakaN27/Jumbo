<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 01.07.15
 * Общий класс для контроллеров консоли
 * для выполнения лбой команды необходимо ввести секретный ключ
 *
 */

namespace console\components;

use yii\console\Controller;
use yii\helpers\Console;

class AbstractConsoleController extends Controller{

    CONST
        ACCESS_SECRET_KEY = '713505f459b1e0f0f401b786f1a428ce'; //web-mart-new

    public function beforeAction($action)
    {
        /*
        $sSecret = $this->prompt('Key:', ['required' => true]);
        if(md5($sSecret) != self::ACCESS_SECRET_KEY)
        {
            $this->stderr('You are not allowed for make this action. Go away!', Console::FG_RED, Console::BOLD);
            echo PHP_EOL;
            exit(1);
        }
        */
        return parent::beforeAction($action);
    }
} 