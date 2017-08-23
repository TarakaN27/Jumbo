<?php
use yii\bootstrap\Modal;
use yii\helpers\Url;
?>
<?php Modal::begin([
	'id' => 'exchange-rates-modal',
	'size' => Modal::SIZE_DEFAULT,
	'options' => [
		'data-url' => Url::to(['/ajax-service/load-exchange-rates'])
	]
]);?>



<?php Modal::end();
?>
