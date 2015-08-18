<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */
use \yii\data\ArrayDataProvider;
use yii\helpers\Html;
?>
<?=\yii\grid\GridView::widget([
        'dataProvider' => new ArrayDataProvider([
                'allModels' => $arData['data'],
            ]),
        'columns' =>[
            'username',
            'fio',
            'cost',
            'units',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model, $key) use ($searchModel) {
                            $options = [
                                'title' => Yii::t('yii', 'View'),
                                'aria-label' => Yii::t('yii', 'View'),
                                'data-pjax' => '0',
                            ];
                            $customUrl = \yii\helpers\Url::to([
                                'view',
                                'dateFrom' => $searchModel->dateFrom,
                                'dateTo' => $searchModel->dateTo,
                                'id' => $model['id']]);
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $customUrl, $options);
                        }
                ]
            ],
        ]
    ])?>
