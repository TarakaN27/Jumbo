<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 01.07.15
 */

namespace console\controllers;


use backend\models\BUser as User;
use console\components\AbstractConsoleController;
use yii\helpers\Console;

class UserController extends AbstractConsoleController {

    /**
     * Show all commands
     */
    public function actionIndex()
    {
        echo 'yii users/create' . PHP_EOL;
        echo 'yii users/remove' . PHP_EOL;
        echo 'yii users/activate' . PHP_EOL;
        echo 'yii users/diactivate' . PHP_EOL;
        echo 'yii users/change-password' . PHP_EOL;
        echo 'yii users/change-role' . PHP_EOL;
    }

    /**
     * Add new user
     * @return int
     */
    public function actionCreate()
    {
        $model = new User();
        $this->readValue($model, 'username');
        $this->readValue($model, 'email');
        $model->setPassword($this->prompt('Password:', [
            'required' => true,
            'pattern' => '#^.{6,255}$#i',
            'error' => 'More than 6 symbols',
        ]));
        $model->generateAuthKey();
        return $this->log($model->save());
    }

    /**
     * Change password
     * @return int
     */
    public function actionChangePassword()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        $model->setPassword($this->prompt('New password:', [
            'required' => true,
            'pattern' => '#^.{6,255}$#i',
            'error' => 'More than 6 symbols',
        ]));
        return $this->log($model->save());
    }

    /**
     * Set active status for user
     * @return int
     */
    public function actionActivate()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        $model->status = User::STATUS_ACTIVE;
        return $this->log($model->save());
    }

    /**
     * Set blocked status for user
     * @return int
     */
    public function actionDiactivate()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        $model->status = User::STATUS_BLOCKED;
        return $this->log($model->save());
    }

    /**
     * @param $success
     * @return int
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!'. PHP_EOL, Console::FG_GREEN, Console::BOLD);
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stderr('Error!'. PHP_EOL, Console::FG_RED, Console::BOLD);
            return self::EXIT_CODE_ERROR;
        }
    }

    /**
     * @param Model $model
     * @param string $attribute
     */
    private function readValue($model, $attribute)
    {
        $model->$attribute = $this->prompt('Input '.mb_convert_case($attribute, MB_CASE_TITLE, 'utf-8') . ':', [
            'validator' => function ($input, &$error) use ($model, $attribute) {
                    $model->$attribute = $input;
                    if ($model->validate([$attribute])) {
                        return true;
                    } else {
                        $error = implode(',', $model->getErrors($attribute));
                        return false;
                    }
                },
        ]);
    }

    /**
     * @param string $username
     * @throws \yii\console\Exception
     * @return User the loaded model
     */
    private function findModel($username)
    {
        if (!$model = User::findOne(['username' => $username])) {
            throw new \yii\console\Exception('User not found');
        }
        return $model;
    }

    /**
     * Change user role
     * @return int
     */
    public function actionChangeRole()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        $this->stdout('Current role is: '.$model->getRoleStr(). PHP_EOL, Console::FG_BLUE, Console::BOLD);
        echo 'Allow role is(Input numeric code of role):' . PHP_EOL;
        foreach(User::getRoleArr() as $key => $value)
        {
            echo $value . ':' . $key . PHP_EOL;
        }
        $this->readValue($model, 'role');
        return $this->log($model->save());
    }

    /**
     * Delete user
     * @return int
     */
    public function actionRemove()
    {
        $username = $this->prompt('Username:', ['required' => true]);
        $model = $this->findModel($username);
        return $this->log($model->delete());
    }

} 