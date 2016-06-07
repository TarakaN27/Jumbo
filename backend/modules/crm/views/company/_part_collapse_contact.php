<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 16.43
 */
use yii\helpers\Html;
use yii\helpers\Url;
use backend\models\BUser;
use yii\helpers\Json;
?>
<div class="project_detail">
	<p class="title"><?= $contact->getAttributeLabel('phone'); ?></p>
	<p>
		<?php echo Html::a($contact->phone,'#',[
			'class' => 'editable',
			'data-type' => "text",
			'data-name' => 'phone',
			'data-pk' => $contact->id,
			'data-url' => Url::to(['edit-contacts']),
			'data-title' => $contact->getAttributeLabel('phone')
		]) ?>
	</p>
	<p class="title"><?= $contact->getAttributeLabel('email'); ?></p>
	<p>
		<?php echo Html::a($contact->email,'#',[
			'class' => 'editable',
			'data-type' => "text",
			'data-name' => 'email',
			'data-pk' => $contact->id,
			'data-url' => Url::to(['edit-contacts']),
			'data-title' => $contact->getAttributeLabel('email')
		]) ?>
	</p>
	<p class="title"><?= $contact->getAttributeLabel('addition_info'); ?></p>
	<p>
		<?php echo Html::a($contact->addition_info,'#',[
			'class' => 'editable',
			'data-type' => "textarea",
			'data-pk' => $contact->id,
			'data-name' => 'addition_info',
			'data-url' => Url::to(['edit-contacts']),
			'data-title' => $contact->getAttributeLabel('addition_info')
		]) ?>
	</p>
	<p class="title"><?= $contact->getAttributeLabel('description'); ?></p>
	<p>
		<?php echo Html::a($contact->description,'#',[
			'class' => 'editable',
			'data-type' => "textarea",
			'data-pk' => $contact->id,
			'data-name' => 'description',
			'data-url' => Url::to(['edit-contacts']),
			'data-title' => $contact->getAttributeLabel('description')
		]) ?>
	</p>
	<p class="title"><?= $contact->getAttributeLabel('assigned_at'); ?></p>
	<p>
		<?php echo is_object($obAss = $contact->assignedAt) ? $obAss->getFio() : $contact->assigned_at;?>
	</p>
</div>
