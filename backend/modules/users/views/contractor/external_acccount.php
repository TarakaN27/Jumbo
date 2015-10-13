<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 1.10.15
 * Time: 11.10
 */
$this->title = Yii::t('app/users','External account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/users', 'Cusers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="">
    <div class="page-title">
        <div class="title_left">
            <h3><?php echo $this->title;?></h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_content">
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-12 animated fadeInDown">
                            <div class="well profile_view">
                                <div class="col-sm-12">
                                    <h4 class="marg-l-10"><i><?php echo Yii::t('app/users','Client System of Digital Agency') ?></i></h4>
                                    <div class="col-md-12 min-height-100">
                                        <p><strong><?php echo Yii::t('app/users','About') ?>: </strong>
                                            <?php echo Yii::t('app/users','requisites for client systems of digital agency') ?>
                                        </p>
                                        <?php if(!empty($csdaAcc)):?>
                                            <table class="table">
                                                <tr>
                                                    <td>
                                                        <?php echo Yii::t('app/users','Login') ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $csdaAcc->login;?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <?php echo  Yii::t('app/users','Password')?>
                                                    </td>
                                                    <td>
                                                        <?php echo $csdaAcc->password;?>
                                                    </td>
                                                </tr>
                                            </table>
                                        <?php else:?>
                                            <?php echo  Yii::t('app/users','Account is not created. Press add account');?>
                                        <?php endif;?>
                                    </div>
                                </div>
                                <div class="col-xs-12 bottom">
                                    <div class="marg-l-10">
                                        <?php if(empty($csdaAcc)): ?>
                                            <?php echo \yii\helpers\Html::a('<i class="fa fa-user"></i> '
                                                .Yii::t('app/users','Create external account'),
                                                ['create-external-account',
                                                    'iCID' => $iCID,
                                                    'type' => \common\models\CuserExternalAccount::TYPE_CSDA
                                                ],
                                                ['class' => 'btn btn-primary btn-xs']
                                            )?>
                                        <?php else:?>
                                            <?echo \yii\helpers\Html::a('<i class="fa fa-user"></i> '
                                                .Yii::t('app/users','Delete external account'),
                                                ['delete-external-account',
                                                    'iCID' => $iCID,
                                                    'iExtID' => $csdaAcc->id,
                                                    'type' => \common\models\CuserExternalAccount::TYPE_CSDA
                                                ],
                                                ['class' => 'btn btn-primary btn-xs'])?>
                                        <?php endif;?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>