<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\ExpenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app/book', 'Expenses');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class = "row">
<div class = "col-md-12 col-sm-12 col-xs-12">
                            <div class = "x_panel">
                                <div class = "x_title">
                                    <h2><?php echo $this->title?></h2>
                                    <section class="pull-right">
                                     <?= Html::a(Yii::t('app/book', 'Create Expense'), ['create'], ['class' => 'btn btn-success']) ?>
                                    </section>
                                    <div class = "clearfix"></div>
                                </div>
                                <div class = "x_content">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'cuser_id',
                'value' => function($model){
                        return is_object($cuser = $model->cuser) ? $cuser->username : 'N/A';
                    },
                'filter' => \common\models\CUser::getContractorMap()
            ],
            [
                'attribute' => 'cat_id',
                'value' => function($model){
                        return is_object($cat = $model->cat) ? $cat->name : 'N/A';
                    },
                'filter' => \common\models\ExpenseCategories::getExpenseCatMap()
            ],

            'pay_summ',
            [
                'attribute' => 'currency_id',
                'value' => function($model){
                        return is_object($curr = $model->currency) ? $curr->code : 'N/A';
                    },
                'filter' => \common\models\ExchangeRates::getRatesCodes()
            ],
            [
                'attribute' => 'legal_id',
                'value' => function($model){
                        return is_object($legal = $model->legal) ? $legal->name : 'N/A';
                    },
                'filter' => \common\models\LegalPerson::getLegalPersonMap()
            ],
            [
                'attribute' => 'pay_date',
                'value' => function($model){
                        return $model->getFormatedPayDate();
                    },
                'filter' => \yii\jui\DatePicker::widget([

                        'model'=>$searchModel,
                        'attribute'=>'pay_date',
                        'language' => 'ru',
                        'dateFormat' => 'dd-MM-yyyy',
                        'options' =>['class' => 'form-control'],
                        'clientOptions' => [
                            'defaultDate' => date('d-m-Y',time())
                        ],
                    ]),
                'format' => 'raw',
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
                             </div>
                            </div>
                        </div>
</div>
