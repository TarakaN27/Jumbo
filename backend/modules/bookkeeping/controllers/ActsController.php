<?php

namespace backend\modules\bookkeeping\controllers;

use common\components\csda\CSDAUser;
use common\models\CUser;
use common\models\CuserExternalAccount;
use common\models\CUserRequisites;
use common\models\LegalPerson;
use Yii;
use common\models\Acts;
use common\models\search\ActsSearch;
use backend\components\AbstractBaseBackendController;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\base\InvalidParamException;
use common\models\ServiceDefaultContract;
use common\models\CuserServiceContract;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ActsController implements the CRUD actions for Acts model.
 */
class ActsController extends AbstractBaseBackendController
{


    /**
     * переопределяем права на контроллер и экшены
     * @return array
     */
    public function behaviors()
    {
        $tmp = parent::behaviors();
        $tmp['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['superadmin','bookkeeper']
                ]
            ]
        ];
        $tmp['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'find-contact-number' => ['post'],
                'find-act-template' => ['post']
            ],
        ];
        return $tmp;
    }

    /**
     * Lists all Acts models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Acts model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Acts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Acts();
        $model->getEntityFields(); //доп поля
        $model->buser_id = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Acts model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario('update');
        $model->getEntityFields(); //доп поля

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Acts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Acts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Acts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Acts::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return array|null
     */
    public function actionFindContactNumber()
    {
        $iCID = Yii::$app->request->post('iCID');
        $iServ = Yii::$app->request->post('iServ');
        $iLP = Yii::$app->request->post('iLP');

        if(!$iCID || !$iServ || !$iLP)
            throw new InvalidParamException('contractor id and service id must be set');

        Yii::$app->response->format = Response::FORMAT_JSON;    //указываем что отдаем json
        /** @var CuserServiceContract $obCSC */
        $obCSC = CuserServiceContract::findOne(['cuser_id' => $iCID,'service_id' => $iServ]);
        if($obCSC && $obCSC->cont_number && $obCSC->cont_date)
            return ['num' => $obCSC->cont_number, 'date' => $obCSC->cont_date];
        /** @var ServiceDefaultContract $obDSC */
        $obDSC = ServiceDefaultContract::findOne(['service_id' => $iServ,'lp_id' => $iLP]);
        if(!$obDSC)
            return NULL;

        return ['num' => $obDSC->cont_number, 'date' => $obDSC->cont_date];

    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionFindActTemplate()
    {
        $iLP = Yii::$app->request->post('iLP');

        if(!$iLP)
            throw new InvalidParamException('contractor id and service id must be set');
        /** @var LegalPerson $obLP */
        $obLP = LegalPerson::findOneByIDCached($iLP);
        Yii::$app->response->format = Response::FORMAT_JSON;    //указываем что отдаем json
        return ['tpl' => $obLP->act_tpl_id];
    }

    /**
     * @param $ask
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionDownloadFile($ask)
    {
        /** @var Acts $obAct */
        $obAct = Acts::findOne(['ask' => $ask]);
        if(!$obAct)
            throw new NotFoundHttpException('Acts not found');

        return $obAct->getDocument();
    }

    /**
     * @todo возможно нужно будет применять очередь сообщений. Например RabbitMQ
     * @return bool
     * @throws NotFoundHttpException
     */
    public function actionSendActs()
    {
        $arSelected = Yii::$app->request->post('selection');
        if(!$arSelected)
            throw new InvalidParamException('Acts not set');

        $arActs = Acts::find()->where(['id' => $arSelected])->all();
        if(!$arActs)
            throw new NotFoundHttpException('Acts not found');

        $arCUser = [];
        /** @var Acts $act */
        foreach($arActs as $act)
            if(!in_array($act->cuser_id,$arCUser))
                $arCUser [] = $act->cuser_id;

        $arCUserEmail = CUser::getCUserEmails($arCUser); //получаем емаил пользователя
        $arSKUsers = CuserExternalAccount::getSKByCUserIDs($arCUser,CuserExternalAccount::TYPE_CSDA); // получаем внешние аккаунты
        if(!$arCUserEmail)
            throw new NotFoundHttpException('Contractor not found');

        foreach($arActs as $act)
        {
            if(isset($arCUserEmail[$act->cuser_id]))
            {
                if(\Yii::$app->mailer->compose( // отправялем уведомление по ссылке
                    [
                        'html' => 'actNotification-html',
                        'text' => 'actNotification-text'
                    ],
                    [
                        'act' => $act]
                    )
                    ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
                    ->setTo($arCUserEmail[$act->cuser_id])
                    ->setSubject('Act notification ' . \Yii::$app->name)
                    ->send()) {

                        if(isset($arSKUsers[$act->cuser_id])) //отпарвляем уведомление на внешний аккаунт
                        {
                            $obCSDA = new CSDAUser();
                            $obCSDA->sentNotificationNewAct($act,$arSKUsers[$act->cuser_id]);
                        }

                        $act->sent = Acts::YES;
                        $act->save();
                }
            }

        }
        Yii::$app->response->format = Response::FORMAT_JSON;    //set answer format
        return TRUE;
    }

}
