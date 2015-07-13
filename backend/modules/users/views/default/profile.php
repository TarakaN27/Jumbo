<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 13.07.15
 */
use yii\helpers\Html;
$this->title = Yii::t('app/users','Profile');
?>
                <div class="">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>User Report <small>Activity report</small></h2>
                                    <section class="pull-right">
                                        <?=Html::a(
                                            '<i class="fa fa-edit m-right-xs"></i>'.Yii::t('app/users','Edit_profile'),
                                            ['edit-profile'],
                                            ['class' => 'btn btn-success']
                                        )?>
                                        <?=Html::a(
                                            '<i class="fa fa-edit m-right-xs"></i>'.Yii::t('app/users','Change_password'),
                                            ['change-own-password'],
                                            ['class' => 'btn btn-success']
                                        )?>
                                    </section>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">

                                    <div class="col-md-3 col-sm-3 col-xs-12 profile_left">

                                        <div class="profile_img">
                                            <!-- end of image cropping -->
                                            <div id="crop-avatar">
                                                <!-- Current avatar -->
                                                <div class="avatar-view" title="Change the avatar">
                                                    <?php echo Html::img('@web/images/defaultUserAvatar.jpg')?>
                                                </div>

                                                <!-- Loading state -->
                                                <div class="loading" aria-label="Loading" role="img" tabindex="-1"></div>
                                            </div>
                                            <!-- end of image cropping -->
                                        </div>
                                        <h3><?php echo $model->getFio();?></h3>

                                        <ul class="list-unstyled user_data">
                                            <li>
                                                <i class="fa fa-briefcase user-profile-icon"></i> <?php echo $model->username;?>
                                            </li>
                                            <li class="m-top-xs">
                                                <i class="fa fa-external-link user-profile-icon"></i>
                                                <a href="mailto:<?=$model->email;?>" target="_blank"><?=$model->email;?></a>
                                            </li>
                                        </ul>
                                        <br />
                                        <!-- start skills -->
                                        <h4>Skills</h4>
                                        <ul class="list-unstyled user_data">
                                            <li>
                                                <p>Web Applications</p>
                                                <div class="progress progress_sm">
                                                    <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="50"></div>
                                                </div>
                                            </li>
                                            <li>
                                                <p>Website Design</p>
                                                <div class="progress progress_sm">
                                                    <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="70"></div>
                                                </div>
                                            </li>
                                            <li>
                                                <p>Automation & Testing</p>
                                                <div class="progress progress_sm">
                                                    <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="30"></div>
                                                </div>
                                            </li>
                                            <li>
                                                <p>UI / UX</p>
                                                <div class="progress progress_sm">
                                                    <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="50"></div>
                                                </div>
                                            </li>
                                        </ul>
                                        <!-- end of skills -->

                                    </div>
                                    <div class="col-md-9 col-sm-9 col-xs-12">

                                        <div class="profile_title">
                                            <div class="col-md-6">
                                                <h2>User Activity Report</h2>
                                            </div>
                                        </div>
                                        <!-- start of user-activity-graph -->
                                        <div id="graph_bar" style="width:100%; height:280px;"></div>
                                        <!-- end of user-activity-graph -->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>