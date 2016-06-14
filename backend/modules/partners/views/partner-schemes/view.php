<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PartnerSchemes */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Partner Schemes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class = "row">
        <div class = "col-md-12 col-sm-12 col-xs-12">
            <div class = "x_panel">
                <div class = "x_title">
                    <h2><?= Html::encode($this->title) ?></h2>
                    <section class="pull-right">
                        <?=  Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a(Yii::t('app/users', 'Create Partner Schemes'), ['create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('app/users', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a(Yii::t('app/users', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                        'confirm' => Yii::t('app/users', 'Are you sure you want to delete this item?'),
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
                                'start_period',
                                'regular_period',
                                [
                                    'attribute' => 'currency_id',
                                    'value' => is_object($obCurr = $model->currency) ? $obCurr->code : NULL
                                ],
                                [
                                    'attribute' => 'turnover_type',
                                    'value' => $model->getTurnoverTypeStr()
                                ],
                                [
                                    'attribute' => 'counting_base',
                                    'value' => $model->getCountingBaseStr()
                                ],
                                'created_at:datetime',
                                'updated_at:datetime',
                            ],
                        ]) ?>
                        <?= \yii\grid\GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider([
                                'allModels' => $modelDetail
                            ]),
                            'columns' => [
                                'id',
                                [
                                    'attribute' => 'service_id',
                                    'value'=> function($model) use ($arServices){
                                        return isset($arServices[$model->service_id]) ? $arServices[$model->service_id] : NULL;
                                    }
                                ],
                                [
                                    'attribute' => 'group_id',
                                    'value' => 'group.name'
                                ],
                                [
                                    'attribute' => 'ranges',
                                    'format' => 'raw',
                                    'value' => function($model){
                                        return \yii\grid\GridView::widget([
                                           'dataProvider' => new \yii\data\ArrayDataProvider([
                                               'allModels' => $model->ranges
                                           ]),
                                           'layout' => "{items}\n{pager}",
                                            'columns' => [
                                                [
                                                    'attribute' => 'left',
                                                    'label' => Yii::t('app/users','Min')
                                                ],
                                                [
                                                    'attribute' => 'right',
                                                    'label' => Yii::t('app/users','Max')
                                                ],
                                                [
                                                    'attribute' => 'percent',
                                                    'label' => Yii::t('app/users','Percent')
                                                ]
                                            ]
                                        ]);
                                    }
                                ],
                                [
                                    'attribute' => 'legal',
                                    'format' => 'raw',
                                    'value' => function($model) use ($arLegal)
                                    {
                                        if(!is_array($model->legal))
                                            return $model->legal;

                                        $head = Html::tag('th',Yii::t('app/bonus','Legal person'));
                                        $head.= Html::tag('th',Yii::t('app/bonus','Deduct VAT'));
                                        $head.= Html::tag('th',Yii::t('app/bonus','Detail'));
                                        $str = Html::tag('thead',Html::tag('tr',$head));
                                        unset($head);

                                        $arLG = $model->legal;
                                        foreach($arLegal as $key=>$value)
                                        {
                                            $bVal = isset($arLG[$key]) && isset($arLG[$key]['deduct']) ? $arLG[$key]['deduct'] : 0;
                                            $tmp = Html::tag('th',$value);
                                            $tmp.= Html::tag('td',Yii::$app->formatter->asBoolean($bVal));
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
                                ]
                            ]

                        ])?>

                </div>
            </div>
        </div>
    </div>
