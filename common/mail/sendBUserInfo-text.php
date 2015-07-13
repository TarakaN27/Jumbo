<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 13.07.15
 */
?>
В системе Webmart зарегистрирован новый пользователь.<br/>
Имя пользователя: <?php echo $user->username;?><br/>
ФИО: <?php echo $user->getFio();?><br/>
Роль: <?php echo $user->getRoleStr();?><br/>