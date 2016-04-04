<?php
use backend\assets\AppAsset;
use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\widgets\sideBarFooter\SideBarFooterWidget;
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

if(!Yii::$app->user->isGuest && (Yii::$app->user->can('only_manager') || Yii::$app->user->can('adminRights')))
{
    $subItems[] = ['label' => Yii::t('app/common', 'Add bill'), 'url' => ['/documents/bills/create']];
    $subItems[] = ['label' => Yii::t('app/common', 'Bill list'), 'url' => ['/documents/bills/index']];

    $menuItems[] = [
        'label' => '<i class="fa fa-list-alt"></i> '.Yii::t('app/common','Bill'),
        'items' => $subItems,
    ];

    unset($subItems);
}

if(!Yii::$app->user->isGuest && Yii::$app->user->can('only_bookkeeper'))
{
    $subItems[] = ['label' => Yii::t('app/common', 'Bill template'), 'url' => ['/documents/bill-template/index']];
    $menuItems[] = [
        'label' => '<i class="glyphicon glyphicon-cog"></i> '.Yii::t('app/common','Settings'),
        'items' => $subItems,
    ];
    unset($subItems);
}

if(!Yii::$app->user->isGuest && Yii::$app->user->can('superRights'))
{
    /*
    $subItems[] = ['label' => Yii::t('app/common', 'CRM roles'), 'url' => ['/config/b-user-crm-roles/index']];
    $subItems[] = ['label' => Yii::t('app/common', 'CRM group'), 'url' => ['/config/b-user-crm-group/index']];
    $menuItems[] = [
        'label' => '<i class="glyphicon glyphicon-cog"></i> '.Yii::t('app/common','CRM'),
        'items' => $subItems,
    ];
    unset($subItems);
*/
    $subItems[] = ['label' => Yii::t('app/common', 'Config'), 'url' => ['/config/default/index']];
    $subItems[] = ['label' => Yii::t('app/common', 'Entity fields'), 'url' => ['/config/entity-fields/index']];
    $subItems[] = ['label' => Yii::t('app/common', 'Units'), 'url' => ['/units/default/index']];
    $subItems[] = ['label' => Yii::t('app/book', 'BOOK_payment_condition'), 'url' => ['/bookkeeping/payment-condition/index']];
    $subItems[] = ['label' => Yii::t('app/common', 'Bill template'), 'url' => ['/documents/bill-template/index']];
    $subItems[] = ['label' => Yii::t('app/common', 'Bill docx template'), 'url' => ['/documents/bill-docx-template/index']];
    $subItems[] = ['label' => Yii::t('app/common', 'Acts template'), 'url' => ['/documents/acts-template/index']];
    //$subItems[] = ['label' => Yii::t('app/common', 'Acts numbers'), 'url' => ['/documents/act-numbers/index']];
    $subItems[] = ['label' => Yii::t('app/common', 'Partner condition'), 'url' => ['/bookkeeping/partner-condition/index']];
    $menuItems[] = [
        'label' => '<i class="glyphicon glyphicon-cog"></i> '.Yii::t('app/common','Settings'),
        'items' => $subItems,
    ];

    unset($subItems);
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
                            <?=Html::a(
                                '<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>',
                                NULL,
                                [
                                    'class' => 'refresh-notification-btn',
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top',
                                    'data-original-title' => Yii::t('app/common','Flush badge notification'),
                                ]
                            )?>
                        </div>
                        <div class = "profile_info">
                            <span><?php echo Yii::t('app/common', 'Welcome') ?>,</span>
                            <h2><?php echo Yii::$app->user->identity->username; ?></h2>

                        </div>


                    </div>
                    <!-- /menu prile quick info -->

                    <br />

                    <!-- sidebar menu -->
                    <div id = "sidebar-menu" class = "main_menu_side hidden-print main_menu pddtop20">

                        <div class = "menu_section">
                            <hr>
                            <ul class = "nav side-menu">
                                <?php if(Yii::$app->user->can('adminRights')):?>
                                    <li>
                                        <a>
                                            <i class = "fa fa-user"></i> <?php echo Yii::t('app/users', 'USER_users'); ?>
                                            <span class = "fa fa-chevron-down"></span>
                                        </a>
                                        <ul class = "nav child_menu" style = "display: none">
                                            <li>
                                                <a href = "<?= Url::to(['/users/default/index']) ?>"><?php echo Yii::t('app/users', 'USER_staf'); ?></a>
                                            </li>
                                            <li>
                                                <?=Html::a(Yii::t('app/common', 'CRM roles'),['/config/b-user-crm-roles/index']);?>
                                            </li>
                                            <li>
                                                <?=Html::a(Yii::t('app/common', 'CRM group'),['/config/b-user-crm-group/index']);?>
                                            </li>

                                            <?php if(Yii::$app->user->can('superRights')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/users/partner/index']) ?>"><?php echo Yii::t('app/users', 'USER_partner'); ?></a>
                                            </li>
                                            <?php endif;?>
                                        </ul>
                                    </li>
                                <?php endif;?>
                                <?php if(
                                Yii::$app->user->can('adminRights') ||
                                Yii::$app->user->can('only_bookkeeper') ||
                                Yii::$app->user->can('only_manager')
                                ):?>
                                <li>
                                    <a><i class = "fa fa-edit"></i><?php echo Yii::t('app/services', 'SERVICES_services_and_expense'); ?>
                                        <span class = "fa fa-chevron-down"></span></a>
                                    <ul class = "nav child_menu" style = "display: none">
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/services/default/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_services'); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if(Yii::$app->user->can('superRights')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/services/expense/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_expense_categories'); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if(Yii::$app->user->can('adminRights')):?>
                                        <li>
                                            <a href = "<?= Url::to(['/services/legal-person/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_legal_person'); ?></a>
                                        </li>
                                        <?php endif; ?>
                                        <?php if(Yii::$app->user->can('forAll')):?>
                                             <li>
                                                <a href = "<?= Url::to(['/services/exchange-rates/index']); ?>"><?php echo Yii::t('app/services', 'SERVICES_exchange_rates'); ?></a>
                                             </li>
                                        <?php endif;?>
                                    </ul>
                                </li>
                                <?php endif;?>
                                <?php if(
                                Yii::$app->user->can('adminRights') ||
                                Yii::$app->user->can('only_bookkeeper') ||
                                Yii::$app->user->can('only_manager')
                                ):?>
                                <li>
                                    <a><i class = "fa fa-desktop"></i><?php echo Yii::t('app/book', 'BOOK_bookkeeping'); ?>
                                        <span class = "fa fa-chevron-down"></span>
                                        <?= \common\components\notification\widget\PaymentRequestWidget::widget();?>
                                        <?= \common\components\notification\widget\EnrollmentRequestWidget::widget();?>
                                    </a>
                                    <ul class = "nav child_menu" style = "display: none">
                                        <!---Запросы на платеж -->
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/payment-request/index']); ?>">
                                                    <?php echo Yii::t('app/book', 'BOOK_payment_request'); ?>
                                                    <?= \common\components\notification\widget\PaymentRequestWidget::widget();?>
                                                </a>
                                            </li>
                                        <?php endif;?>
                                        <!---Платежи ------->
                                        <?php if(Yii::$app->user->can('forAll')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/default/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_payments'); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <!---Запросы на зачисление ------->
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <?=Html::a(Yii::t('app/common','Enrollment request'),['/bookkeeping/enrollment-request/index'])?>
                                                <?= \common\components\notification\widget\EnrollmentRequestWidget::widget();?>
                                            </li>
                                        <?php endif;?>
                                        <!---Зачисления---------------->
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <?=Html::a(Yii::t('app/common','Enrollments'),['/bookkeeping/enrolls/index'])?>
                                            </li>
                                        <?php endif;?>
                                        <!---Обещанные платежи-------->
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/promised-payment/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_promised_payment'); ?></a>
                                            </li>
                                        <?php endif;?>
                                        <!---Условия для платежей---------->
                                        <?php if(Yii::$app->user->can('superRights')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/payment-condition/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_payment_condition'); ?></a>
                                            </li>
                                        <?php endif;?>
                                        <!---Затраты ---------------------->
                                        <?php if(Yii::$app->user->can('superRights') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/expense/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_expense'); ?></a>
                                            </li>
                                        <?php endif;?>
                                        <!---Акты и вывод средст партнера--------->
                                        <?php if(Yii::$app->user->can('superRights') || Yii::$app->user->can('only_bookkeeper')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/acts/index']); ?>"><?php echo Yii::t('app/book', 'BOOK_acts'); ?></a>
                                            </li>
                                            <li>
                                                <a href = "<?= Url::to(['/bookkeeping/partner-withdrawal/index']); ?>">
                                                    <?php echo Yii::t('app/book', 'BOOK_partner_withdrawal'); ?>
                                                </a>
                                            </li>
                                        <?php endif;?>
                                    </ul>
                                </li>
                                <?php endif;?>
                                <li><a><i class="fa fa-bar-chart-o"></i><?php echo Yii::t('app/common', 'Reports'); ?> <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu" style="display: none">
                                        <li>
                                            <a href="<?= Url::to(['/reports/calendar/index']); ?>"><?php echo Yii::t('app/common', 'Calendar'); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?= Url::to(['/reports/timesheet/index']); ?>"><?php echo Yii::t('app/common', 'Timesheet'); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?= Url::to(['/reports/bonus-report/index']); ?>"><?php echo Yii::t('app/common', 'Bonus report'); ?></a>
                                        </li>
                                        <?php if(Yii::$app->user->can('adminRights')):?>
                                            <li>
                                                <a href="<?= Url::to(['/reports/payments-report/index']); ?>"><?php echo Yii::t('app/common', 'Payments reports'); ?></a>
                                            </li>
                                        <?php endif;?>
                                    </ul>
                                </li>
                                <?php if(Yii::$app->user->can('adminRights')):?>
                                <li>
                                    <a>
                                        <i class="fa fa-gift"></i><?php echo Yii::t('app/common', 'Remuneration'); ?>
                                        <span class = "fa fa-chevron-down"></span>
                                    </a>
                                    <ul class = "nav child_menu" style = "display: none">

                                        <li>
                                            <a href = "<?= Url::to(['/bonus/default/index']); ?>"><?php echo Yii::t('app/common', 'Bonus scehemes'); ?></a>
                                        </li>

                                    </ul>
                                </li>
                                <?php endif;?>
                                <li><a>
                                        <i class="fa fa-cube"></i><?php echo Yii::t('app/common', 'CRM'); ?>
                                        <span class="fa fa-chevron-down"></span>
                                        <?= \common\components\notification\widget\TaskNewWidget::widget();?>
                                        <?=\common\components\notification\widget\DialogNewWidget::widget();?>
                                        <?=\common\components\notification\widget\CompanyNewWidget::widget();?>
                                        <?=\common\components\notification\widget\ContactNewWidget::widget();?>

                                    </a>
                                    <ul class="nav child_menu" style="display: none">
                                        <li>
                                            <a href="<?= Url::to(['/crm/default/index']); ?>">
                                                <?php echo Yii::t('app/common', 'List feed'); ?>
                                                <?=\common\components\notification\widget\DialogNewWidget::widget();?>
                                            </a>
                                        </li>
                                        <?php if(
                                        Yii::$app->user->can('adminRights') ||
                                        Yii::$app->user->can('only_bookkeeper') ||
                                        Yii::$app->user->can('only_manager') ||
                                        Yii::$app->user->can('only_jurist') ||
                                        Yii::$app->user->can('only_e_marketer')
                                        ):?>
                                        <li>
                                            <a href="<?= Url::to(['/crm/company/index']); ?>">
                                                <?php echo Yii::t('app/common', 'Company'); ?>
                                                <?=\common\components\notification\widget\CompanyNewWidget::widget();?>
                                            </a>
                                        </li>
                                        <?php endif;?>
                                        <?php if(Yii::$app->user->can('adminRights') || Yii::$app->user->can('only_manager')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/users/user-groups/index']) ?>">&minus;&minus;<?php echo Yii::t('app/users', 'USER_cuser_groups'); ?></a>
                                            </li>
                                        <?php endif;?>
                                        <?php if(Yii::$app->user->can('adminRights')):?>
                                            <li>
                                                <a href = "<?= Url::to(['/users/user-types/index']) ?>">&minus;&minus;<?php echo Yii::t('app/users', 'USER_cuser_types'); ?></a>
                                            </li>
                                            <li>
                                                <a href = "<?= Url::to(['/users/cuser-prospects/index']) ?>">&minus;&minus;<?php echo Yii::t('app/users', 'USER_cuser_prospects'); ?></a>
                                            </li>
                                        <?php endif;?>
                                        <?php if(
                                        Yii::$app->user->can('adminRights') ||
                                        Yii::$app->user->can('only_bookkeeper') ||
                                        Yii::$app->user->can('only_manager') ||
                                        Yii::$app->user->can('only_e_marketer')
                                        ):?>
                                        <li>
                                            <a href="<?= Url::to(['/crm/contact/index']); ?>">
                                                <?php echo Yii::t('app/common', 'Contacts'); ?>
                                                <?=\common\components\notification\widget\ContactNewWidget::widget();?>
                                            </a>
                                        </li>
                                        <?php endif;?>
                                        <li>
                                            <a href="<?= Url::to(['/crm/task/index']); ?>">
                                                <?php echo Yii::t('app/common', 'Tasks'); ?>
                                                <?= \common\components\notification\widget\TaskNewWidget::widget();?>
                                            </a>
                                        </li>

                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?=SideBarFooterWidget::widget();?>
                </div>
            </div>

            <!-- top navigation -->
            <div class = "top_nav no-print">

                <div class = "nav_menu">
                    <nav class = "navbar" role = "navigation">
                        <div class = "nav toggle">
                            <a id = "menu_toggle"><i class = "fa fa-bars"></i></a>
                        </div>
                        <?php
                            if(Yii::$app->user->getLogWorkType() == \backend\models\BUser::LOG_WORK_TYPE_TIMER)
                                echo \backend\components\widgets\WorkDay\WorkDayWidget::widget()
                        ?>
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
    <!--div id = "custom_notifications" class = "custom-notifications dsp_none no-print">
        <ul class = "list-unstyled notifications clearfix" data-tabbed_notifications = "notif-group">
        </ul>
        <div class = "clearfix"></div>
        <div id = "notif-group" class = "tabbed_notifications"></div>
    </div-->
    <?php echo \common\components\notification\widget\TabledNotificationWidget::widget();?>

    <?php $this->endBody() ?>
    <?php if(!Yii::$app->user->isGuest):?>
        <!-- jira bug tracking -->
        <script type="text/javascript" src="http://jira.webmartsoft.com/s/d41d8cd98f00b204e9800998ecf8427e/en_USdj7yrs-1988229788/6256/3/1.4.7/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=6d25f919"></script>
        <script type="text/javascript" src="http://jira.webmartsoft.com/s/d41d8cd98f00b204e9800998ecf8427e/en_USdj7yrs-1988229788/6256/3/1.4.7/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=2fdbcde1"></script>
        <script>
            window.ATL_JQ_PAGE_PROPS =  {
                // ==== we add the code below to set the field values ====
                fieldValues : {
                    fullname : '<?php echo Yii::$app->user->identity->username;?>'
                    , email : '<?php echo Yii::$app->user->identity->email;?>',
                    versions : '10719'
                },
                // ==== Special field config for environment ====
                environment : {
                    'YII_DEBUG'  : '<?=YII_DEBUG;?>',
                    'YII_ENV' : '<?=YII_ENV;?>'
                }
            };
        </script>
    <?php endif;?>

</body>
</html>
<?php $this->endPage() ?>
