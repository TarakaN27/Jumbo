<?php
namespace backend\controllers;

use backend\models\forms\BUserSignupForm;
use common\models\BuserInviteCode;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use backend\models\LoginForm;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

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
                        'actions' => ['logout', 'index'],
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
}
