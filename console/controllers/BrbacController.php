<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 05.12.14
 */

namespace console\controllers;
use console\components\AbstractConsoleController;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use backend\components\rbac\UserRoleRule;
/**
 * Generate users rights
 */
class BrbacController extends AbstractConsoleController {

	/**
	 * @return int
	 * выводим описание и примеры команд
	 */
	public function actionIndex()
	{
		echo 'This is commands for work with back end users. Create rights' . PHP_EOL;
		echo 'yii brbac/create' . PHP_EOL;
		return Controller::EXIT_CODE_NORMAL;
	}

    /**
     * Create user rules and rights
     * @return int
     */
    public function actionCreate()
	{
		$this->stdout('Start!'. PHP_EOL, Console::FG_BLUE, Console::BOLD);
		$this->stdout('Init authManager'. PHP_EOL, Console::FG_YELLOW);
		$auth = Yii::$app->authManager;
		$this->stdout('OK'. PHP_EOL, Console::FG_GREEN);
		$this->stdout('Remove old file'. PHP_EOL, Console::FG_YELLOW);
		$auth->removeAll(); //удаляем старые данные
		$this->stdout('OK'. PHP_EOL, Console::FG_GREEN);


		//Создаем правила
		$this->stdout('Create permission'. PHP_EOL, Console::FG_YELLOW);

		$superRights = $auth->createPermission('superRights');
		$superRights->description = 'Права супер пользователя';

		$onlyManager = $auth->createPermission('only_manager');
		$onlyManager->description = 'Только для менеджера';

		$adminRights = $auth->createPermission('adminRights');
		$adminRights->description = 'Админские права';

		$onlyBookkeeper = $auth->createPermission('only_bookkeeper');
        $onlyBookkeeper->description = 'Только для бухгалтера';


		$auth->add($onlyBookkeeper);
		$auth->add($adminRights);
		$auth->add($superRights);
		$auth->add($onlyManager);

		$this->stdout('OK'. PHP_EOL, Console::FG_GREEN);


		$this->stdout('Include handler'. PHP_EOL, Console::FG_YELLOW);
		//Включаем наш обработчик
		$rule = new UserRoleRule();
		$auth->add($rule);
		$this->stdout('OK'. PHP_EOL, Console::FG_GREEN);

		$this->stdout('Adding role'. PHP_EOL, Console::FG_YELLOW);
		//Добавляем роли
		//user
		$user = $auth->createRole('user');
		$user->description = 'Пользователь';
		$user->ruleName = $rule->name;
		$auth->add($user);

		//moder
		$moder = $auth->createRole('moder');
		$moder->description = 'Модератор';
		$moder->ruleName = $rule->name;
		$auth->add($moder);

        //moder
        $bookkeeper = $auth->createRole('bookkeeper');
        $bookkeeper->description = 'Бухгалтер';
        $bookkeeper->ruleName = $rule->name;
        $auth->add($bookkeeper);

		//admin
		$admin = $auth->createRole('admin');
		$admin->description = 'Администратор';
		$admin->ruleName = $rule->name;
		$auth->add($admin);

		//superaadmin
		$sadmin = $auth->createRole('superadmin');
		$sadmin->description = 'Супер администратор';
		$sadmin->ruleName = $rule->name;
		$auth->add($sadmin);



		//Добавляем разрешения для ролей

        //bookkeeper
        $auth->addChild($bookkeeper, $user);
        $auth->addChild($bookkeeper, $onlyBookkeeper);

		//moder
		$auth->addChild($moder, $user);
		$auth->addChild($moder, $onlyManager);
		//$auth->addChild($moder, $onlyBookkeeper);

		//admin
		$auth->addChild($admin, $user);
		$auth->addChild($admin, $adminRights);

		//superAdmin
		$auth->addChild($sadmin, $admin);
		$auth->addChild($sadmin, $superRights);

		$this->stdout('OK'. PHP_EOL, Console::FG_GREEN);
		$this->stdout('Success!'. PHP_EOL, Console::FG_GREEN, Console::BOLD);

		return Controller::EXIT_CODE_NORMAL;
	}

} 