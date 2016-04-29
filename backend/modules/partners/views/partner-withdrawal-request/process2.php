<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.4.16
 * Time: 13.26
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;

$this->title = Yii::t('app/users','Partner withdrawal request');
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <?php echo \yii\widgets\DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                [
                                    'attribute' => 'created_by',
                                    'value' => is_object($obCrBy = $model->createdBy) ? $obCrBy->getFio() : NULL
                                ],
                                'amount:decimal',
                                [
                                    'attribute' => 'currency_id',
                                    'value' => is_object($obCurr = $model->currency) ? $obCurr->code : NULL
                                ],
                                'date:date',
                                [
                                    'attribute' => 'type',
                                    'value' => $model->getTypeStr()
                                ],
                                [
                                    'attribute' => 'status',
                                    'value' => $model->getStatusStr()
                                ],
                                'created_at:datetime',
                                'description:text'
                            ]
                        ])?>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12">

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <?php $form=ActiveForm::begin([

                        ]);?>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <?=$form->field($model,'manager_id')->widget(\kartik\select2\Select2::className(),[
                                    'initValueText' => $manDesc, // set the initial display text
                                    'options' => [
                                        'placeholder' => Yii::t('app/crm','Search for a users ...')
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 2,
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['/ajax-select/get-b-user']),
                                            'dataType' => 'json',
                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new JsExpression('function(cmp_id) { return cmp_id.text; }'),
                                        'templateSelection' => new JsExpression('function (cmp_id) { return cmp_id.text; }'),
                                    ],
                                ])?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class = "col-md-12 col-sm-12 col-xs-12">
                                <?= Html::submitButton(Yii::t('app/users', 'Save'), ['class' => 'btn btn-success']) ?>
                            </div>
                        </div>
                        <?php ActiveForm::end();?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
