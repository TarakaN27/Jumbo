<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.12.15
 * Time: 16.43
 */
use yii\helpers\Html;
?>
<div class="project_detail">
	<p class="title"><?= $contact->getAttributeLabel('phone'); ?></p>
	<p><?=$contact->phone;?></p>
	<p class="title"><?= $contact->getAttributeLabel('email'); ?></p>
	<p><?=$contact->email;?></p>
	<p class="title"><?= $contact->getAttributeLabel('addition_info'); ?></p>
	<p><?=$contact->addition_info;?></p>
	<p class="title"><?= $contact->getAttributeLabel('description'); ?></p>
	<p><?=$contact->description;?></p>
	<p class="title"><?= $contact->getAttributeLabel('assigned_at'); ?></p>
	<p><?=is_object($obAss = $contact->assignedAt) ? $obAss->getFio() : $contact->assigned_at;?></p>
</div>
