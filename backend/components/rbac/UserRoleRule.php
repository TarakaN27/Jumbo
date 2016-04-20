<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 02.07.15
 */

namespace backend\components\rbac;
use Yii;
use yii\rbac\Rule;

use backend\models\BUser as User;

class UserRoleRule extends Rule{

    public $name = 'userRole';
    public function execute($user, $item, $params)     {
        //check the role from table user
        if(isset(Yii::$app->user->identity->role))
            $role = Yii::$app->user->identity->role;
        else
            return false;

        switch ($item->name) {
            case 'superadmin':
                return $role == User::ROLE_SUPERADMIN;
                break;
            case 'admin':
                return $role == User::ROLE_SUPERADMIN || $role == User::ROLE_ADMIN;
                break;
            case 'partner_manager':
                return $role == User::ROLE_PARTNER_MANAGER || $role == User::ROLE_MANAGER;
                break;
            case 'moder':
                return $role == User::ROLE_MANAGER;
                break;
            case 'bookkeeper':
                return $role == User::ROLE_BOOKKEEPER;
                break;
            case 'jurist':
                return $role == User::ROLE_JURIST ;
                break;
            case 'e_marketer':
                return $role == User::ROLE_E_MARKETER ;
                break;
            case 'user':
                return
                    $role == User::ROLE_SUPERADMIN ||
                    $role == User::ROLE_ADMIN ||
                    $role == User::ROLE_BOOKKEEPER ||
                    $role == User::ROLE_MANAGER ||
                    $role == User::ROLE_PARTNER_MANAGER ||
                    $role == User::ROLE_USER ||
                    $role == User::ROLE_JURIST ||
                    $role == User::ROLE_E_MARKETER;
                break;
            default:
                return FALSE;
        }

    }

} 