<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.12.15
 * Time: 16.05
 */
use yii\bootstrap\Html;
?>
<?foreach($models as $model):?>
<li>
	<img src="/service/images/defaultUserAvatar.jpg" class="avatar" alt="Avatar">
	<div class="message_date">
		<h3 class="date text-info"><?=Date('d',$model->created_at);?></h3>
		<p class="month"><?=Date('M',$model->created_at);?></p>
	</div>
	<div class="message_wrapper">
		<h4 class="heading"><?=is_object($obUser = $model->owner) ? $obUser->getFio() : $model->buser_id;?></h4>
		<blockquote class="message">
			<?=$model->theme;?>
		</blockquote>
		<br />
		<p class="url">
			<span class="fs1 text-info" aria-hidden="true" data-icon=""></span>
			<a data-id="<?=$model->id;?>" class="btn-show-hide" href="#"><?=Yii::t('app/crm','Show messages')?> <i class="fa fa-chevron-down"></i></a>
		</p>

	</div>
	<ul class="list-unstyled msg_list need-load" data-id="<?=$model->id;?>">

	</ul>
</li>
<?endforeach;?>
<?php if(!is_null($pag)):?>
	<?php if($pag->getPageCount() > $pag->getPage()+1): $links = $pag->getLinks();?>
		<?=Html::button('load more',[
			'data-url' => $links[\yii\data\Pagination::LINK_NEXT],
			'class' => 'btn btn-default btn-load-more'
		])?>
	<?php endif;?>

<?endif;?>
