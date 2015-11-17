<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 16.11.15
 * Time: 15.25
 */
use yii\helpers\Html;
use yii\helpers\Url;
$parse = parse_url(Url::home(true));
$actLink = $parse['scheme'].'://'.$parse['host'].'/site/get-act-pdf?ask='.$act->ask;
?>
<div class="">
	<p>Добрый день.</p>

	<p>В клиентской системе Вебмарт Групп был сгенерирован новый акт.</p>

	<p>Для скачивания акта пройдите по ссылке <?= Html::a(Html::encode($actLink), $actLink) ?></p>
</div>
