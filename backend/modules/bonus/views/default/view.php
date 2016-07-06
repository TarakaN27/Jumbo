<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\BonusScheme;
use yii\data\ArrayDataProvider;
use common\components\customComponents\collapse\CollapseWidget;
use yii\grid\GridView;
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
                        $arItem[] = [
                            'label' => Yii::t('app/bonus','Cusers'),
                            'content' => \yii\grid\GridView::widget([
                                'dataProvider' => new ArrayDataProvider([
                                    'allModels' => $arCusers
                                ]),
                                'columns' => [
                                    'id',
                                    'infoWithSite'
                                ]
                            ])
                        ];



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

                        if($model->type != BonusScheme::TYPE_PAYMENT_RECORDS)
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
                                            $head.= Html::tag('th',Yii::t('app/bonus','Detail'));
                                            $str = Html::tag('thead',Html::tag('tr',$head));
                                            unset($head);

                                            $arLG = $model->legal_person;
                                            foreach($arLegal as $key=>$value)
                                            {
                                                $bVal = isset($arLG[$key]) && isset($arLG[$key]['deduct']) ? $arLG[$key]['deduct'] : 0;
                                                $tmp = Html::tag('th',$value);
                                                $tmp.= Html::tag('td',Yii::$app->formatter->asBoolean($bVal));$tmpTable = '';
                                                $tmpTable = '';
                                                if($bVal)
                                                {
                                                    $tmpBody = '';
                                                    $head = Html::tag('th','');
                                                    $head.= Html::tag('th',Yii::t('app/bonus','deduct tax'));
                                                    $head.= Html::tag('th',Yii::t('app/bonus','Custom tax'));
                                                    $tmpHead = Html::tag('thead',Html::tag('tr',$head));
                                                    $tmpTd = Html::tag('td',Yii::t('app/bonus','Resident'));
                                                    $tmpTd.= Html::tag('td',isset($arLG[$key]) && isset($arLG[$key]['res']) ? Yii::$app->formatter->asBoolean($arLG[$key]['res']) : '');
                                                    $tmpTd.= Html::tag('td',isset($arLG[$key]) && isset($arLG[$key]['res_tax']) ? $arLG[$key]['res_tax'] : '');
                                                    $tmpBody.=Html::tag('tr',$tmpTd);
                                                    $tmpTd = Html::tag('td',Yii::t('app/bonus','Not resident'));
                                                    $tmpTd.= Html::tag('td',isset($arLG[$key]) && isset($arLG[$key]['not_res']) ? Yii::$app->formatter->asBoolean($arLG[$key]['not_res']) : '');
                                                    $tmpTd.= Html::tag('td',isset($arLG[$key]) && isset($arLG[$key]['not_res_tax']) ? $arLG[$key]['not_res_tax'] : '');
                                                    $tmpBody.=Html::tag('tr',$tmpTd);
                                                    $tmpTable = Html::tag('table',Html::tag('tbody',$tmpHead.$tmpBody),['class' => 'table table-bordered']);
                                                }
                                                $tmp.=Html::tag('td',$tmpTable);
                                                $str.=Html::tag('tr',$tmp);
                                            }
                                            return Html::tag('table',Html::tag('tbody',$str),['class' => 'table table-bordered']);
                                        }
                                    ],

                                ]
                            ])
                        ];

                        if($model->type == BonusScheme::TYPE_PAYMENT_RECORDS)
                            $arItem [] = [
                                'label' => Yii::t('app/bonus','Detail'),
                                'content'=>
                                    Html::tag('h3',Yii::t('app/bonus','Rates record bonus')).
                                    GridView::widget([
                                    'dataProvider' => new ArrayDataProvider([
                                        'allModels' => is_object($arRecordRates) ? $arRecordRates->params : []
                                    ]),
                                    'columns' => [
                                        [
                                            'attribute' => 'from',
                                            'label' => Yii::t('app/bonus','from')
                                        ],
                                        [
                                            'attribute' => 'to',
                                            'label' => Yii::t('app/bonus','to')
                                        ],
                                        [
                                            'attribute' => 'rate',
                                            'label' => Yii::t('app/bonus','Rate')
                                        ]
                                    ]
                                ]).
                                Html::tag('h3',Yii::t('app/bonus','Deduct tax at legal person')).
                                $this->render('_part_record_deduct_tax',[
                                    'model' => is_object($arRecordRates) ? $arRecordRates->deduct_lp : [],
                                    'arLegal' => $arLegal
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
