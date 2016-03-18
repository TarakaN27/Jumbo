<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.3.16
 * Time: 14.32
 */
?>
<?php if($data):?>
<ul>
	<?php foreach($data as $d):?>
	<li>
		<?=\yii\helpers\Html::a($d->getInfo(),['/crm/company/view','id' => $d->id]);?>
	</li>
	<?php endforeach;?>
</ul>
<?php endif;?>
