<?php
use backend\assets\AppAsset;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);

//Собираем верхнее меню!
$subItems[] = ['label' => Yii::t('app/users', 'Profile'), 'url' => ['/users/default/profile']];
$subItems[] = [
    'label' => '<i class="fa fa-sign-out pull-right"></i>' . Yii::t('app/common', 'Logout'),
    'url' => ['/site/logout'],
    'linkOptions' => ['data-method' => 'post']
];

$menuItems[] = [
    'label' => Html::img('@web/images/defaultUserAvatar.jpg') . Yii::$app->user->identity->username,
    'items' => $subItems,
    'options' => [
        'class' => 'user-profile'
    ]
];
unset($subItems);

if(!Yii::$app->user->isGuest && Yii::$app->user->can('superRights'))
{
    $subItems[] = ['label' => Yii::t('app/common', 'Units'), 'url' => ['/units/default/index']];
    $subItems[] = ['label' => Yii::t('app/book', 'BOOK_payment_condition'), 'url' => ['/bookkeeping/payment-condition/index']];

    $menuItems[] = [
        'label' => '<i class="glyphicon glyphicon-cog"></i> '.Yii::t('app/common','Settings'),
        'items' => $subItems,
    ];

    unset($subItems);
}

if(!Yii::$app->user->isGuest && Yii::$app->user->can('only_manager'))
{
    $menuItems[] = [
        'label' => '<i class="fa fa-money"></i> '.Yii::t('app/common','Units'),
        'url' => ['/units/units-manager/index']
    ];
}


$menuItems[] = [
    'label' => '<i class="fa fa-envelope-o"></i> '.Yii::t('app/common','Messages'),
    'url' => ['/messenger/default/index']
];

//должен всегда идти последним
$menuItems[] = [
    'label' => '<i class="fa fa-university"></i> '.Yii::t('app/common','To dashboard'),
    'url' => Yii::$app->homeUrl
];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang = "<?= Yii::$app->language ?>">
<head>
    <meta charset = "<?= Yii::$app->charset ?>">
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class = "nav-md">
    <?php $this->beginBody() ?>
    <div class = "container body">
         <div class = "main_container">

            <div class = "col-md-3 left_col">
                <div class = "left_col scroll-view">

                    <div class = "navbar nav_title" style = "border: 0;">
                        <a href = "<?php echo Yii::$app->homeUrl; ?>"
                           class = "site_title"><?php echo Html::img('@web/images/logo.png', ['alt' => 'Webmart Logo']); ?>
                            <span>Webmart Group.</span></a>
                    </div>
                    <div class = "clearfix"></div>

                    <!-- menu prile quick info -->
                    <div class = "profile">
                        <div class = "profile_pic">
                            <?php echo Html::img('@web/images/defaultUserAvatar.jpg', ['class' => 'img-circle profile_img']); ?>
                        </div>
                        <div class = "profile_info">
                            <span><?php echo Yii::t('app/common', 'Welcome') ?>,</span>
                            <h2><?php echo Yii::$app->user->identity->username; ?></h2>
                        </div>
                    </div>
                    <!-- /menu prile quick info -->

                    <br />

                    <!-- sidebar menu -->
                    <div id = "sidebar-menu" class = "main_menu_side hidden-print main_menu">

                        <div class = "menu_section">
                            <h3>General</h3>
                            <ul class = "nav side-menu">
                                <li><a><i class = "fa fa-user"></i> <?php echo Yii::t('app/users', 'USER_users'); ?>
                                        <span class = "fa fa-chevron-down"></span></a>
                                    <ul class = "nav child_menu" style = "display: none">
                                        <li><a href = "<?= Url::to(['/users/default/index']) ?>"><?php echo Yii::t('app/users', 'USER_staf'); ?></a>
                                        </li>
                                        <li><a href = "<?= Url::to(['/users/contractor/index']) ?>"><?php echo Yii::t('app/users', 'USER_contractor'); ?></a>
                                        </li>
                                        <?php if(Yii::$app->user->can('adminRights')):?>
                                        <li>
                                            <a href = "<?= Url::to(['/users/user-types/index']) ?>">&minus;&minus;<?php echo Yii::t('app/users', 'USER_cuser_types'); ?></a>
                                        </li>
                                        <?php endif;?>
                                    </ul>
                                </li>
                                <li><a><i class = "fa fa-edit"></i><?php echo Yii::t('app/services', 'SERVICES_services_and_expense'); ?>
                                        <span class = "fa fa-chevron-down"></span></a>
                                    <ul class = "nav child_menu" style = "display: none">
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/services/default/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_services'); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/services/expense/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_expense_categories'); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a href = "<?= Url::to(['/services/legal-person/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_legal_person'); ?></a>
                                        </li>
                                        <?php if(Yii::$app->user->can('forAll')):?>
                                             <li>
                                                <a href = "<?= Url::to(['/services/exchange-rates/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_exchange_rates'); ?></a>
                                             </li>
                                        <?php endif;?>
                                    </ul>
                                </li>
                                <li><a><i class = "fa fa-desktop"></i><?php echo Yii::t('app/book', 'BOOK_bookkeeping'); ?>
                                        <span class = "fa fa-chevron-down"></span></a>
                                    <ul class = "nav child_menu" style = "display: none">
                                        <?php if(Yii::$app->user->can('forAll')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/default/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_payments'); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/expense/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_expense'); ?></a>
                                            </li>
                                        <?php endif;?>
                                        <?php if(Yii::$app->user->can('superRights') || Yii::$app->user->can('only_manager') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/payment-request/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_payment_request'); ?></a>
                                            </li>
                                        <?php endif;?>
                                        <?php if(Yii::$app->user->can('superRights')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/payment-condition/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_payment_condition'); ?></a>
                                            </li>
                                        <?php endif;?>
                                    </ul>
                                </li>
                                <!--li><a><i class="fa fa-envelope"></i> <?php echo Yii::t('app/common', 'MSG_dialogs'); ?> <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu" style="display: none">
                                        <li>
                                            <a href="<?= Url::to(['/messenger/default/index']); ?>"><?php echo Yii::t('app/common', 'MSG_messages'); ?></a>
                                        </li>
                                    </ul>
                                </li-->
                                <?php if(Yii::$app->user->can('adminRights')):?>
                                <li><a><i class="fa fa-bar-chart-o"></i> <?php echo Yii::t('app/common', 'Reports'); ?> <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu" style="display: none">
                                         <li>
                                            <a href="<?= Url::to(['/reports/payments-report/index']); ?>"><?php echo Yii::t('app/common', 'Payments reports'); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?= Url::to(['/reports/units-reports/index']); ?>"><?php echo Yii::t('app/common', 'Units reports'); ?></a>
                                        </li>
                                    </ul>
                                </li>
                                <?php endif;?>
                            </ul>
                        </div>
                        <!--div class="menu_section">
                            <h3>Live On</h3>
                            <ul class="nav side-menu">
                                <li><a><i class="fa fa-bug"></i> Additional Pages <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu" style="display: none">
                                        <li><a href="e_commerce.html">E-commerce</a>
                                        </li>
                                        <li><a href="projects.html">Projects</a>
                                        </li>
                                        <li><a href="project_detail.html">Project Detail</a>
                                        </li>
                                        <li><a href="contacts.html">Contacts</a>
                                        </li>
                                        <li><a href="profile.html">Profile</a>
                                        </li>
                                    </ul>
                                </li>
                                <li><a><i class="fa fa-windows"></i> Extras <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu" style="display: none">
                                        <li><a href="page_404.html">404 Error</a>
                                        </li>
                                        <li><a href="page_500.html">500 Error</a>
                                        </li>
                                        <li><a href="plain_page.html">Plain Page</a>
                                        </li>
                                        <li><a href="login.html">Login Page</a>
                                        </li>
                                        <li><a href="pricing_tables.html">Pricing Tables</a>
                                        </li>

                                    </ul>
                                </li>
                                <li><a><i class="fa fa-laptop"></i> Landing Page <span class="label label-success pull-right">Coming Soon</span></a>
                                </li>
                            </ul>
                        </div-->

                    </div>
                    <!-- /sidebar menu -->

                </div>
            </div>

            <!-- top navigation -->
            <div class = "top_nav no-print">

                <div class = "nav_menu">
                    <nav class = "navbar" role = "navigation">
                        <div class = "nav toggle">
                            <a id = "menu_toggle"><i class = "fa fa-bars"></i></a>
                        </div>
                        <?php echo Nav::widget([
                            'encodeLabels' => FALSE,
                            'options' => ['class' => 'navbar-nav navbar-right'],
                            'items' => $menuItems,
                        ]);?>
                    </nav>
                </div>

            </div>
            <!-- /top navigation -->


            <!-- page content -->
            <div class = "right_col" role = "main">
                <div class = "clearfix"></div>
                <?= \backend\widgets\Alert::widget(); ?>
                <?= $content ?>
                <!-- footer content -->
                <footer class="no-print">
                    <div class = "">
                        <p class = "pull-right">Webmart Group corp! <?= Yii::powered() ?> <a>Webmart Group</a>. |
                            <span class = "lead"><?php echo Html::img('@web/images/logo.png', ['alt' => 'Webmart Logo']); ?>
                                Webmart Group!</span>
                        </p>
                    </div>
                    <div class = "clearfix"></div>
                </footer>
                <!-- /footer content -->
            </div>
            <!-- /page content -->

        </div>
    </div>
    <div id = "custom_notifications" class = "custom-notifications dsp_none no-print">
        <ul class = "list-unstyled notifications clearfix" data-tabbed_notifications = "notif-group">
        </ul>
        <div class = "clearfix"></div>
        <div id = "notif-group" class = "tabbed_notifications"></div>
    </div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
