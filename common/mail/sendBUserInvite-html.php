<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 13.07.15
 */
use yii\helpers\Html;

$inviteLink = Yii::$app->urlManager->createAbsoluteUrl(['/site/sign-up', 'code' => $code]);
?>
    <p>Уважаемый пользователь!</p>

    <p>Вам было отправлено приглашение на регистрацию в Webmart System.</p>
    <p>Пройдите по ссылке и зарегистрируйтесь</p>
    <p><?= Html::a(Html::encode($inviteLink), $inviteLink) ?></p>
    <br/>
    <p><i>ВНИМАНИЕ!</i><br>Если вы не занете кто вам выслал это приглашение или что такое Webmart System, пожалуйста удалите это письмо.</p>
</div>