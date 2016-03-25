<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\BonusScheme;
use yii\data\ArrayDataProvider;
use common\components\customComponents\collapse\CollapseWidget;
/* @var $this yii\web\View */
/* @var $model common\models\BonusScheme */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/bonus', 'Bonus Schemes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/bonus', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/bonus', 'Create Bonus Scheme'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('app/bonus', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/bonus', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                        'confirm' => Yii::t('app/bonus', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                        ],
                        ]) ?>
                    </section>
                    <div class = "clearfix"></div>
                </div>
                <div class = "x_content">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                'name',
                                [
                                    'attribute' => 'type',
                                    'value' => $model->getTypeStr()
                                ],
                                'num_month',
                                [
                                    'attribute' => 'grouping_type',
                                    'value' => $model->getGroupingTypeStr()
                                ],
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>

                    <?
                        $arItem [] = [
                            'label' => Yii::t('app/bonus','Users'),
                            'content'=>\yii\grid\GridView::widget([
                                'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $arUsers
                                ]),
                                'columns' => [
                                    'fio',
                                    'roleStr'
                                ]
                            ])
                        ];

                        $arItem [] = [
                            'label' => Yii::t('app/bonus','Detail'),
                            'content'=>\yii\grid\GridView::widget([
                                'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $arBServices
                                ]),
                                'columns' => [
                                    [
                                        'attribute' => 'service_id',
                                        'value' => function($model) use ($arServices){
                                            return isset($arServices[$model->service_id]) ? $arServices[$model->service_id] : $model->service_id;
                                        }
                                    ],
                                    [
                                        'attribute' => 'cost',
                                        'format' => 'decimal',
                                        'visible' => $model->type == BonusScheme::TYPE_UNITS
                                    ],
                                    [
                                        'attribute' => 'unit_multiple',
                                        'format' => 'boolean',
                                        'visible' => $model->type == BonusScheme::TYPE_UNITS
                                    ],
                                    [
                                        'attribute' => 'simple_percent',
                                        'format' => 'decimal',
                                        'visible' => $model->type == BonusScheme::TYPE_SIMPLE_BONUS,
                                    ],
                                    [
                                        'attribute' => 'month_percent',
                                        'format' => 'raw',
                                        'visible' => $model->type == BonusScheme::TYPE_COMPLEX_TYPE,
                                        'value' => function($model){
                                            if(!is_array($model->month_percent))
                                                return $model->month_percent;
                                            $head = Html::tag('th',Yii::t('app/bonus','Month number'));
                                            $head.= Html::tag('th',Yii::t('app/bonus','Percent'));
                                            $str = Html::tag('thead',Html::tag('tr',$head));
                                            unset($head);
                                            foreach($model->month_percent as $key=>$value)
                                            {
                                                $tmp = Html::tag('th',$key);
                                                $tmp.= Html::tag('td',Yii::$app->formatter->asDecimal($value));
                                                $str.=Html::tag('tr',$tmp);
                                            }
                                            return Html::tag('table',Html::tag('tbody',$str),['class' => 'table table-bordered']);
                                        }
                                    ],
                                    [
                                        'attribute' => 'legal_person',
                                        'format' => 'raw',
                                        'visible' => in_array($model->type,[BonusScheme::TYPE_COMPLEX_TYPE,BonusScheme::TYPE_SIMPLE_BONUS]),
                                        'value' => function($model) use ($arLegal){
                                            if(!is_array($model->legal_person))
                                                return $model->legal_person;

                                            $head = Html::tag('th',Yii::t('app/bonus','Legal person'));
                                            $head.= Html::tag('th',Yii::t('app/bonus','Deduct VAT'));
                                            $str = Html::tag('thead',Html::tag('tr',$head));
                                            unset($head);

                                            $arLG = $model->legal_person;
                                            foreach($arLegal as $key=>$value)
                                            {
                                                $bVal = isset($arLG[$key]) ? $arLG[$key] : 0;
                                                $tmp = Html::tag('th',$value);
                                                $tmp.= Html::tag('td',Yii::$app->formatter->asBoolean($bVal));
                                                $str.=Html::tag('tr',$tmp);
                                            }
                                            return Html::tag('table',Html::tag('tbody',$str),['class' => 'table table-bordered']);
                                        }
                                    ],

                                ]
                            ])
                        ];
                    ?>
                    <?php echo CollapseWidget::widget([
                        'items' => $arItem
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
