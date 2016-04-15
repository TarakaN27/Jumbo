<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 14.4.16
 * Time: 15.11
 */
use yii\helpers\Html;
use yii\grid\GridView;
$this->title = Yii::t('app/users','Partner link lead')
?>
<div class = "row">
    <div class = "col-md-12 col-sm-12 col-xs-12">
        <div class = "x_panel">
            <div class = "x_title">
                <h2><?php echo Html::encode($this->title)?></h2>
                <section class="pull-right">
                    <?=Html::a(Yii::t('app/users','Add link'),
                        ['add-link','pid' => $pid],
                        [
                            'class' => 'btn btn-success'
                        ]
                    )?>
                </section>
                <div class = "clearfix"></div>
            </div>
            <div class = "x_content">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,

                ]); ?>
            </div>
        </div>
    </div>
</div>