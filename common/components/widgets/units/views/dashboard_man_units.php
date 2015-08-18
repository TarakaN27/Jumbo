<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */
$this->registerCss('
.dash-u-detail-link-section{
    text-align:center;
}
');
?>
<div class = "col-md-6 col-xs-12 widget widget_tally_box">
    <div class = "x_panel ui-ribbon-container ">
        <div class = "x_title">
            <h2><?= Yii::t('app/common', 'Units') ?></h2>
            <div class = "clearfix"></div>
        </div>
        <div class = "x_content">
            <h4 class=" name_title">
                <?=Yii::$app->formatter->asDate(\common\components\helpers\CustomHelper::getBeginMonthTime())?>
                -
                <?= Yii::$app->formatter->asDate(time()) ?>
            </h4>
            <table class = "table">
                <tbody>
                    <tr>
                        <td>
                            <?= Yii::t('app/common', 'Total units') ?>
                        </td>
                        <td>
                            <?= $iTotalUnits; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= Yii::t('app/common', 'Total summ') ?>
                        </td>
                        <td>
                            <?= $iTotalCost; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class = "divider"></div>
            <section class = "dash-u-detail-link-section">
                <?=\yii\helpers\Html::a(
                    Yii::t('app/common', 'Detail stat') .' <i class="fa fa-external-link-square"></i>',
                    ['/units/units-manager/index']
                )?>
            </section>
        </div>
    </div>
</div>