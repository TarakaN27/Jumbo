<?php
namespace backend\controllers;

use backend\models\forms\BUserSignupForm;
use common\components\anubis\Anubis;
use common\components\bonus\BonusRecordCalculate;
use common\components\crunchs\bonus\ImportSale;
use common\components\crunchs\bonus\RecalculateBonus;
use common\components\crunchs\bonus\RecalculateUnitBonus;
use common\components\crunchs\exchange_rates\ExchangeRatesCrunch;
use common\components\crunchs\Payment\RecalcPayment;
use common\components\crunchs\Payment\RecalcQuantityHours;
use common\components\crunchs\task\ReportTaskUserStat;
use common\components\ExchangeRates\ExchangeRatesNBRB;
use common\components\helpers\CustomHelper;
use common\components\infoHelpers\ManagersInfo;
use common\components\notification\TabledNotification;
use common\components\partners\PartnerPercentCounting;
use common\components\partners\PartnerPercentRecounting;
use common\components\rabbitmq\Rabbit;
use common\components\rabbitmq\workers\ActsLetterRabbitHandler;
use common\components\tasks\RecurringTask;
use common\models\BuserInviteCode;
use common\models\ExchangeCurrencyHistory;
use common\models\Payments;
use Gears\Pdf;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use backend\models\LoginForm;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
/**
 * Site controller
 */
class SiteController extends Controller
{

    public
        $layout = 'login_layout';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error','sign-up'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index','get-document','test-notification','special'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'main';
        return $this->render('index');
    }


    public function actionSpecial()
    {
        /*
        $obMailer = new Mailer([
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.yandex.ru',
                'username' => 'sales@webmart.by',
                'password' => 'web-mart',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ]);

        $obMailer->compose( // отправялем уведомление по ссылке
                [
                    'html' => 'actNotification-html',
                    'text' => 'actNotification-text'
                ]
            )
            ->setFrom(['sales@webmart.by' => \Yii::$app->name . ' robot'])
            //->setTo($toEmail)
            ->setTo('motuzdev@gmail.com')
            ->setBcc('sales@webmart.by')
            ->setSubject('Акт об оказанных услугах ООО "Вебмарт Групп"')
            ->attach($documentPath)
            ->send();
        */
        //\Yii::$app->rabbit->sendMessage(Rabbit::QUEUE_ACTS_SEND_LETTER,['first' => 'test amq']);

        /*
        $recalc = new RecalcPayment();
        $recalc->recalculateWithSetConditions();
        */
        //импорт продаж
        /*
        $obImport = new ImportSale([
            '@backend/runtime/sale_feb.csv',
            '@backend/runtime/sale_jun.csv'
        ]);
        $obImport->run();
        */

        //перерасчет юнитов

        //$obUnit = new RecalculateUnitBonus();
        //$obUnit->run();
        /*
        $obBonus = new RecalculateBonus();
        $obBonus->run();
        */
        //перерассчет бонусов
        /*
        $obBonus = new RecalculateBonus();
        $obBonus->recalculatePartnerBonus();
        */
        /*
        $obCalc = new PartnerPercentCounting();
        $obCalc->countPercentByMonth('01-0-2016');
        */
        //1466340220
        /*
        $obRecurringTask = new RecurringTask(1465389820);
        $obRecurringTask->run();
        */
        /*
        $obTaskStat = new ReportTaskUserStat();
        $obTaskStat->userInfoTaskLoadBalance();
        */
        //восстановление курсов валют по датам

        //$ob = new ExchangeRatesCrunch();
        //$ob->RecoveryExchangeRates('2016-07-01','2016-07-05');

        //$ob = new ExchangeCurrencyHistory();
        //$ob->getCurrencyInByrForPeriod(strtotime('2016-01-01'),strtotime('2016-06-08'),[1,3]);
        /*
        $obCurr = new ExchangeRatesNBRB();
        $obCurr->getCurrencyRateByPeriod(145,strtotime('2016-01-01'),strtotime('2016-06-08'));
        */
        /*
        // перерасчет платежей по правильному курсу валют
        $obPayRcl = new RecalcPayment();
        $obPayRcl->recalculatePayments();
        */
        /*
        $obRecalPer = new PartnerPercentRecounting();
        $obRecalPer->dailyRecounting();
        */
        /*
        $obCalc = new BonusRecordCalculate('01-06-2016');
        $obCalc->run();
        */

        die;
    }


    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionSignUp($code)
    {
        /** @var BuserInviteCode $obInvite */
        $obInvite = BuserInviteCode::findOne(['code' => $code,'status' => BuserInviteCode::NORMAL]);
        if(empty($obInvite) || !$obInvite->isTokenValid($code))
            Throw new ForbiddenHttpException(Yii::t('app/common','You are not allowed to perform this action.'));

        $model = new BUserSignupForm([
            'role' => $obInvite->user_type,
            'email' => $obInvite->email,
            'obInvite' => $obInvite
        ]);
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    $infoEmail = Yii::$app->params['infoEmail'];
                    \Yii::$app->mailer->compose('sendBUserInfo-text',['user' => $user])
                        ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
                        ->setTo($infoEmail)
                        ->setSubject('Registered new user ' . \Yii::$app->name)
                        ->send();

                    BuserInviteCode::updateAll(['status' => BuserInviteCode::BROKEN],'code = :code',[':code' => $code]);
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * @param $name
     * @param $hidfold
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGetDocument($name,$hidfold)
    {
        $fileName = Yii::getAlias('@backend/web/').$hidfold.'/'.$name;

        // если файла нет
        if (!file_exists($fileName)) {
            throw new NotFoundHttpException('File not found');
        }

        // получим размер файла
        $fsize = filesize($fileName);
        // дата модификации файла для кеширования
        $ftime = date("D, d M Y H:i:s T", filemtime($fileName));

        // смещение от начала файла
        $range = 0;
        // пробуем открыть
        $handle = @fopen($fileName, "rb");

        // если не удалось
        if (!$handle){
            throw new ForbiddenHttpException('Access denied');
        }

        // Если запрашивающий агент поддерживает докачку
        if (isset($_SERVER["HTTP_RANGE"]) && $_SERVER["HTTP_RANGE"]) {
            $range = $_SERVER["HTTP_RANGE"];
            $range = str_replace("bytes=", "", $range);
            $range = str_replace("-", "", $range);
            // смещаемся по файлу на нужное смещение
            if ($range) fseek($handle, $range);
        }

        // если есть смещение
        if ($range) {
            header("HTTP/1.1 206 Partial Content");
        } else {
            header("HTTP/1.1 200 OK");
        }

        header("Content-Disposition: attachment; filename=\"{$name}\"");
        header("Last-Modified: {$ftime}");
        header("Content-Length: ".($fsize-$range));
        header("Accept-Ranges: bytes");
        header("Content-Range: bytes {$range}-".($fsize - 1)."/".$fsize);

        // подправляем под IE что б не умничал
        if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
            Header('Content-Type: application/force-download');
        else
            Header('Content-Type: application/octet-stream');

        while(!feof($handle)) {
            $buf = fread($handle,512);
            print($buf);
        }

        fclose($handle);
        Yii::$app->end(200);
    }

    public function actionTestNotification()
    {

        //echo Yii::$app->getUrlManager()->getHostInfo();
        //die;
        $name = 'TEST TITLE';
        $message = 'test messages';

        TabledNotification::addMessage($name,$message,TabledNotification::TYPE_BROADCAST);
        TabledNotification::addMessage(
            $name,$message,
            TabledNotification::TYPE_PRIVATE,TabledNotification::NOTIF_TYPE_ERROR,
            ['3']
            );
    }
}
