<?php
use yii\bootstrap\Modal;
use yii\helpers\Url;
?>
<?php Modal::begin([
	'id' => 'exchange-rates-modal',
	'header' => '<h2>'.Yii::t('app/common','Exchange rates').'</h2>'.\yii\jui\DatePicker::widget([
            'id'=>'curr-date',
            'dateFormat' => 'yyyy-MM-dd',
            'value' => date('Y-m-d'),
            'options' =>['class' => 'form-control'],
        ]),
	'size' => Modal::SIZE_DEFAULT,
	'options' => [
		'data-url' => Url::to(['/ajax-service/load-exchange-rates'])
	]
]);?>



<?php Modal::end();
?>
