<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.12.15
 * Time: 16.05
 */
use yii\bootstrap\Html;
use vova07\imperavi\Widget as ImperaviWidget;
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
			<a data-id="<?=$model->id;?>" class="btn-show-hide"><span><?=Yii::t('app/common','SHOW_MSG_TEXT')?></span> <i class="fa fa-chevron-down"></i></a>
		</p>

	</div>
	<div class="message_wrapper ">
		<ul class="list-unstyled msg_list need-load" data-id="<?=$model->id;?>">

		</ul>
	</div>
	<div class="message_wrapper form-add-msg <?=$uniqStr?>" data-id="<?=$model->id;?>">
		<form onsubmit = "return false;" class = "msgBox" data-id = "<?=$model->id;?>">
			<?php echo Html::hiddenInput('dialog_id', $model->id); ?>
			<?php echo Html::hiddenInput('author_id', Yii::$app->user->id); ?>
			<div class = "x_content">
				<?php echo Html::label(Yii::t('app/common','Message'))?>
				<?php echo ImperaviWidget::widget([
					'name' => 'redactor',
					'settings' => [
						'lang' => 'ru',
						'minHeight' => 200,
						'plugins' => [
							'clips',
							'fullscreen'
						]
					]
				]);?>
				<br />
				<div class = "form-group">
					<button class = "btn btn-success btn-sm addCmpMsg" data = "<?=$model->id;?>" type = "button">
						<?= Yii::t('app/common', 'Add message') ?>
					</button>
				</div>
			</div>
		</form>
	</div>
</li>
<?endforeach;?>
<?php if(!is_null($pag)):?>
	<?php if($pag->getPageCount() > $pag->getPage()+1): $links = $pag->getLinks();?>
		<?=Html::button(Yii::t('app/common','Load more'),[
			'data-url' => $links[\yii\data\Pagination::LINK_NEXT],
			'class' => 'btn btn-default btn-load-more'
		])?>
	<?php endif;?>

<?endif;?>
