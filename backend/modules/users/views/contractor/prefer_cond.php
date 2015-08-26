<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 26.08.15
 */
use yii\helpers\Html;
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?php echo $this->title;?></h2>
                <section class="pull-right">
                    <?= Html::a(Yii::t('app/users', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
            </div>
            <?$form = \yii\bootstrap\ActiveForm::begin([]);?>
            <?php foreach($services as $key=>$serv):?>
                <table class="table">
                    <tr>
                        <td><?=$serv;?></td>
                        <td><?=Html::dropDownList(
                                'service['.$key.']',
                                $arSelected[$key],
                                \common\models\PaymentCondition::getConditionMap(),
                                [
                                    'prompt' => Yii::t('app/book','Choose condition')
                                ]
                            )?></td>
                    </tr>
                </table>
            <?php endforeach;?>

            <div class="form-group">
                <div class = "col-md-offset-8 pull-right">
                    <?= Html::submitButton(Yii::t('app/users', 'Create'), ['class' =>'btn btn-success']) ?>
                </div>
            </div>
            <?\yii\bootstrap\ActiveForm::end()?>
        </div>
    </div>
</div>