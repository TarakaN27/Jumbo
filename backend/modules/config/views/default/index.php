<?

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app/common', 'Config');
$this->params['breadcrumbs'][] = $this->title;


$this->registerCssFile('//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css');
$this->registerJsFile(
    '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js',
    ['depends' => [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ]]
);

$this->registerJs("
$('.editable').editable({
    clear: false,
    validate: function(value) {
		if($.trim(value) == '') {
			return 'This field is required';
		}
	}
});
",\yii\web\View::POS_READY);

?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'name',
                        'alias',
                        [
                            'attribute' => 'value',
                            'format' => 'raw',
                            'value' => function($model){
                                return Html::a($model->value,'#',[
                                    'class' => 'editable',
                                    'data-type' => "text",
                                    'data-pk' => $model->id,
                                    'data-url' => Url::to(['update']),
                                    'data-title' => Yii::t('app/common','New value')
                                ]);
                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
