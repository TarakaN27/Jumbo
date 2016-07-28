<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ExchangeRatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
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
$this->title = Yii::t('app/services', 'Exchange Rates');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "page-title">
    <div class = "title_left">
         <h3><?php $this->title?></h3>
    </div>

    <div class = "title_right">

    </div>
</div>
<div class = "clearfix"></div>
<div class = "row">

<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo $this->title?></h2>
                                    <section class="pull-right">
                                    <?php if(Yii::$app->user->can('adminRights') ||Yii::$app->user->can('only_bookkeeper')):?>
                                        <?= Html::a(Yii::t('app/services', 'Create Exchange Rates'), ['create'], ['class' => 'btn btn-success']) ?>
                                    <?php endif;?>
                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">

    <?php

    $tpl = '';
    $viewTpl = '';
    if(Yii::$app->user->can('adminRights') ||Yii::$app->user->can('only_bookkeeper'))
    {
        $tpl = '{delete}';
        $viewTpl = '{view}';
    }elseif(Yii::$app->user->can('only_manager')){
        $viewTpl = '{view}';
    }
    echo \common\components\widgets\WMCPageSize\WMCPageSize::widget();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'filterSelector' => 'select[name="per-page"]',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'format' => 'html',
                'value' => function($model)
                    {
                        if(Yii::$app->user->can('adminRights') ||Yii::$app->user->can('only_bookkeeper'))
                            return Html::a($model->name,['update','id'=>$model->id],['class'=>'link-upd']);
                        else
                            return $model->name;
                    }
            ],
            'code',
            'nbrb',
            'cbr',
            [
                'attribute' => 'nbrb_rate',
                'label' => Yii::t('app/common','Rate BYN'),
                'value' => function($model){
                    return Yii::$app->formatter->asDecimal($model->nbrb_rate,4);
                }
            ],
            [
                'attribute' => 'nbrb_rate_old',
                'format' => 'decimal',
                'label' => Yii::t('app/common','Rate BYR'),
                'value' => function($model){
                    return round((float)$model->nbrb_rate*10000);
                }
            ],
            //'nbrb_rate',
            [
                'attribute' => 'cbr_rate',
                'value' => function($model){
                    return Yii::$app->formatter->asDecimal($model->cbr_rate,4);
                }
            ],
            [
                'attribute' => 'show_at_widget',
                'format' => 'raw',
                'value' => function($model){
                    $value = is_null($model) ? NULL : Yii::$app->formatter->asBoolean($model->show_at_widget);
                    if(Yii::$app->user->can('adminRights') ||Yii::$app->user->can('only_bookkeeper'))
                        return Html::a($value,'#',[
                            'class' => 'editable',
                            'data-type' => "select",
                            'data-source' => "{0: 'Нет', 1: 'Да'}",
                            'data-pk' => $model->id,
                            'data-url' => Url::to(['update-show-in-widget']),
                            'data-title' => Yii::t('app/service','Show at widget')
                        ]);
                    else
                        return $value;
                },
                'filter' => \common\models\ExchangeRates::getYesNo()
            ],
            [
                'attribute' => 'updated_at',
                'value' => function($model){
                        return $model->getFormatedUpdatedAt();
                    }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> $viewTpl
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> $tpl
            ],
        ],
    ]); ?>

                                </div>
                            </div>
                        </div>
</div>
