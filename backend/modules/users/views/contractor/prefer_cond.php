<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 26.08.15
 */
use yii\helpers\Html;
$this->title = 'Условия по-умолчанию';
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo $this->title;?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'To list'), ['/crm/company/index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <div class = "x_content">
            <?$form = \yii\bootstrap\ActiveForm::begin([]);?>
                <div class="row">
                <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <table class="table table-bordered">
            <?php foreach($services as $key=>$serv):?>

                        <tr>
                            <td><?=$serv;?></td>
                            <td><?=Html::dropDownList(
                                    'service['.$key.']',
                                    $arSelected[$key],
                                    \common\models\PaymentCondition::getConditionMap(),
                                    [
                                        'prompt' => Yii::t('app/book','Choose condition'),
                                        'class' => 'form-control'
                                    ]
                                )?></td>
                        </tr>

            <?php endforeach;?>
                    </table>
                </div></div></div>
                <div class="row">
            <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?= Html::submitButton(Yii::t('app/users', 'save'), ['class' =>'btn btn-success']) ?>
                </div>
            </div>
                </div>
            <?\yii\bootstrap\ActiveForm::end()?>
                </div>
        </div>
    </div>
</div>