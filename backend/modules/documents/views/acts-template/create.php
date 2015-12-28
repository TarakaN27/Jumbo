<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ActsTemplate */

$this->title = Yii::t('app/documents', 'Create Acts Template');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/documents', 'Acts Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?= Html::encode($this->title) ?></h2>
                <section class="pull-right">
                    <?=  Html::a(Yii::t('app/documents', 'To list'), ['index'], ['class' => 'btn btn-warning']) ?>
                </section>
                <div class="clearfix"></div>
                <p>
                    <?=Html::a('<i class="fa fa-warning"></i> '.
                        Yii::t('app/documents','Template field description'),
                        'http://wiki.webmart.by/pages/viewpage.action?pageId=2556123',
                        [
                            'class' => 'colorYellow',
                            'target' => '_blank'
                        ])
                    ?>
                </p>
            </div>
            <div class="x_content acts-template-create">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
