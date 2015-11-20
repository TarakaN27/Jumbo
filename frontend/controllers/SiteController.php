<?php
namespace frontend\controllers;

use common\models\Acts;
use common\models\managers\BillsManager;
use console\components\controllerHelper\ManagerMsg;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @param $bsk
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionGetPdf($bsk)
    {
        if(!$bsk)
            throw new NotFoundHttpException('File not found');
        /** @var BillsManager $obBill */
        $obBill = BillsManager::findOne(['bsk' => $bsk]);

        if(!$obBill)
            throw new NotFoundHttpException('File not found');

        $obBill->getDocument(BillsManager::TYPE_DOC_PDF);
        Yii::$app->end();
    }

    /**
     * @param $ask
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionGetActPdf($ask)
    {
        if(!$ask)
            throw new NotFoundHttpException('File not found');
        /** @var Acts $obAct */
        $obAct = Acts::getOneByAsk($ask,TRUE);

        if(!$obAct)
            throw new NotFoundHttpException('File not found');

        return $obAct->getDocument();
    }
}
