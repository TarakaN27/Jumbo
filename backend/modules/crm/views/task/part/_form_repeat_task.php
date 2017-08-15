<?php
$label1 = ['class' => 'control-label'];
$template1 = '{label}{input}<ul class="parsley-errors-list" >{error}</ul>';
$this->registerJsFile('@web/js/parts/form_repeat_task.js',['depends' => ['yii\web\YiiAsset', 'yii\bootstrap\BootstrapAsset']]);
$template2 = '{label}<div class="col-md-9 col-sm-9 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>';

$labelTemplate1 = ['class' => 'control-label col-md-6 col-sm-6 col-xs-12'];
$template3 = '<div class="form-group">{label}<div class="col-md-6 col-sm-6 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul></div>';


?>
<?=$form->field($model,'repeat_task')->radioList(\common\models\CrmTask::getYesNo());?>

<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3 well repeatTaskBlock hide">
    <div class="col-md-3 col-sm-3 col-xs-12 border-line-right">
        <?=$form->field($obTaskRepeat,'type',[
            'template' => '{label}<div class="col-md-12col-sm-12 col-xs-12">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
        ])->radioList(\common\models\CrmTaskRepeat::getTypeMap())->label(false)?>
    </div>
    <div class="col-md-9 col-sm-9 col-xs-12">
        <!--daily-->
        <?=$form->field($obTaskRepeat,'everyday',[
            'options' => [
                'class' => 'form-group pdd-left-10 blockDaily',
            ],
        ])->checkbox(['class' => 'everyDayType'])?>
        <?=$form->field($obTaskRepeat,'everyday_custom',[
             'options' => [
                'class' => 'form-group pdd-left-10 blockDaily ',
             ],
        ])->checkbox(['class' => 'everyDayType'])?>
        <?=$form->field($obTaskRepeat,'everyday_value',[
            'template' => $template1,
            'labelOptions' => $label1,
            'options' => [
                'class' => 'form-group pdd-left-10 blockDaily',
            ],
        ])->textInput([
            'disabled' => 'disabled'
        ])?>
        <!--weekly-->
        <?=$form->field($obTaskRepeat,'week',[
            'options' => [
                'class' => 'form-group pdd-left-10 blockWeekly',
            ],
            'template' => '{label}<div class="">{input}</div><ul class="parsley-errors-list" >{error}</ul>',
            'labelOptions' => ['class' => 'control-label '],
        ])->textInput([])?>

        <?=$form->field($obTaskRepeat,'monday',[
            'options' => [
                'class' => 'form-group blockWeekly col-md-4 col-sm-4 col-xs-12',
            ],
            'template' => $template1,
            'labelOptions' => $label1,
        ])->checkbox(['class' => 'weekDay'])?>
        <?=$form->field($obTaskRepeat,'tuesday',[
            'options' => [
                'class' => 'form-group blockWeekly col-md-4 col-sm-4 col-xs-12',
            ],
            'template' => $template1,
            'labelOptions' => $label1,
        ])->checkbox(['class' => 'weekDay'])?>
        <?=$form->field($obTaskRepeat,'wednesday',[
            'options' => [
                'class' => 'form-group blockWeekly col-md-4 col-sm-4 col-xs-12',
            ],
            'template' => $template1,
            'labelOptions' => $label1,
        ])->checkbox(['class' => 'weekDay'])?>
        <?=$form->field($obTaskRepeat,'thursday',[
            'options' => [
                'class' => 'form-group blockWeekly col-md-4 col-sm-4 col-xs-12',
            ],
            'template' => $template1,
            'labelOptions' => $label1,
        ])->checkbox(['class' => 'weekDay'])?>
        <?=$form->field($obTaskRepeat,'friday',[
            'options' => [
                'class' => 'form-group blockWeekly col-md-4 col-sm-4 col-xs-12',
            ],
            'template' => $template1,
            'labelOptions' => $label1,
        ])->checkbox(['class' => 'weekDay'])?>
        <?=$form->field($obTaskRepeat,'saturday',[
            'options' => [
                'class' => 'form-group blockWeekly col-md-4 col-sm-4 col-xs-12',
            ],
            'template' => $template1,
            'labelOptions' => $label1,
        ])->checkbox(['class' => 'weekDay'])?>
        <?=$form->field($obTaskRepeat,'sunday',[
            'options' => [
                'class' => 'form-group blockWeekly col-md-4 col-sm-4 col-xs-12',
            ],
            'template' => $template1,
            'labelOptions' => $label1,
        ])->checkbox(['class' => 'weekDay'])?>

        <!--monthly-->
        <section class="blockMonthly">
            <?php
            /*
            $form->field($obTaskRepeat,'monthly_type',[
                'options' => [
                    'class' => 'form-group blockMonthly'
                ],
                'template' => $template2
            ])->radioList(\common\models\CrmTaskRepeat::getMonthlyTypeMap(),['class' => 'monthlyType'])->label(false)
            */
            ?>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <?=$form->field($obTaskRepeat,'day',[
                    'options' => [
                        'class' => 'form-group blockMonthly mon1'
                    ],
                    'template' => $template3,
                    'labelOptions' => $labelTemplate1
                ])->textInput()?>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <?=$form->field($obTaskRepeat,'month',[
                    'options' => [
                        'class' => 'form-group blockMonthly mon1'
                    ],
                    'template' => $template3,
                    'labelOptions' => $labelTemplate1
                ])->textInput()?>
            </div>
            <?php
                /*
            ?>
            <?=$form->field($obTaskRepeat,'number_of_item',[
                'options' => [
                    'class' => 'form-group blockMonthly mon2'
                ]
            ])->dropDownList(\common\models\CrmTaskRepeat::getNumberItemMap())?>
            <?=$form->field($obTaskRepeat,'monthly_days',[
                'options' => [
                    'class' => 'form-group blockMonthly mon2'
                ]
            ])->dropDownList(\common\models\CrmTaskRepeat::getMonthlyDays())?>
            <?=$form->field($obTaskRepeat,'month',[
                'options' => [
                    'class' => 'form-group blockMonthly mon2'
                ]
            ])->textInput()?>
                <?php
                    */
                ?>
        </section>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <hr>
        <?= $form->field($obTaskRepeat,'start_date')->widget(\kartik\date\DatePicker::className(),[
            'options' => ['placeholder' => 'Select operating time ...'],
            //'convertFormat' => true,
            'pluginOptions' => [
                'autoclose'=>true,
                'format' => '  d.m.yyyy',
                'startDate' => date('d.m.yyyy',time()),
                'todayHighlight' => true
            ]
        ])?>
        
        <?=$form->field($obTaskRepeat,'end_type',[
            'template' => $template2
        ])->radioList(\common\models\CrmTaskRepeat::getEndTypeMap(),['class' => 'label-margin-right-10']);?>

        <?=$form->field($obTaskRepeat,'count_occurrences')->textInput()?>
        <?=$form->field($obTaskRepeat,'end_date')->widget(\kartik\date\DatePicker::className(),[
            'options' => ['placeholder' => 'Select operating time ...'],
            //'convertFormat' => true,
            'pluginOptions' => [
                'autoclose'=>true,
                'format' => '  d.m.yyyy',
                'startDate' => date('d.m.yyyy',time()),
                'todayHighlight' => true
            ]
        ])?>
        
    </div>
</div>
