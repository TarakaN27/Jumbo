<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 09.07.15
 */
use yii\helpers\Html;
$this->title = Yii::t('app/users', 'Update Cuser Requisites');
?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2><?php echo $this->title;?></h2>
                                     <section class="pull-right">
                                    <?= Html::a(Yii::t('app/users', 'Back'), ['view','id'=>$userID], ['class' => 'btn btn-warning']) ?>
                                    </section>
                                    <div class="clearfix"></div>
</div>
<?= $this->render('_form_requisites', [
    'model' => $model,
    'modelU' => $modelU
]) ?>

</div></div></div>