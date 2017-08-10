<?php

namespace backend\modules\bookkeeping\controllers;

use backend\modules\bookkeeping\form\ActForm;
use backend\widgets\Alert;
use common\components\csda\CSDAUser;
use common\components\helpers\CustomHelper;
use common\components\rabbitmq\Rabbit;
use common\models\ActImplicitPayment;
use common\models\ActToPayments;
use common\models\CUser;
use common\models\CuserExternalAccount;
use common\models\LegalPerson;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Yii;
use common\models\Acts;
use common\models\search\ActsSearch;
use backend\components\AbstractBaseBackendController;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\base\InvalidParamException;
use common\models\ServiceDefaultContract;
use common\models\CuserServiceContract;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

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
                    'roles' => ['superadmin','bookkeeper','moder']
                ]
            ]
        ];
        $tmp['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'check-act-number' => ['post'],
                'get-next-act-number' => ['post']
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
        $iTotal = $searchModel->countTotal(Yii::$app->request->queryParams);
        $cuserDesc = '';
        if($searchModel->cuser_id)
            $cuserDesc = CUser::getCuserInfoById($searchModel->cuser_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'iTotal' => $iTotal,
            'cuserDesc' =>$cuserDesc
        ]);
    }

    /**
     * Displays a single Acts model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataProvider = new ActiveDataProvider([
            'query' => ActToPayments::find()->joinWith('payment')->where(['act_id' => $model->id]),
            'pagination' => [
                'defaultPageSize' => 1000,
                'pageSizeLimit' => [1,1000]
            ],
        ]);
        $dataProviderImplicid = new ActiveDataProvider([
            'query' =>ActImplicitPayment::find()
            ->joinWith('service')
            ->where(['act_id' => $model->id]),
            'pagination' => [
                'defaultPageSize' => 1000,
                'pageSizeLimit' => [1,1000]
            ],
        ]);


        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'dataProviderImplicid' => $dataProviderImplicid
        ]);
    }

    /**
     * Creates a new Acts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ActForm();
        $model->actDate = Yii::$app->formatter->asDate('NOW');
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {

            if($model->makeRequest())
            {
                Yii::$app->session->addFlash(Alert::TYPE_SUCCESS,Yii::t('app/book','Act successfully created'));
                return $this->redirect(['index']);
            }
        }

        $contractorInitText = '';
        if($model->iCUser)
            $contractorInitText = CUser::getCuserInfoById($model->iCUser);
        
        return $this->render('create',[
            'model' => $model,
            'contractorInitText' => $contractorInitText
        ]);
    }

    /**
     * Deletes an existing Acts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $transation = Yii::$app->db->beginTransaction();
        try {
            $this->findModel($id)->delete();
            $transation->commit();
        }catch (Exception $e)
        {
            $transation->rollBack();
        }

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
     * @todo Нужно будет применять очередь сообщений. Например RabbitMQ
     * @return bool
     * @throws NotFoundHttpException
     */
    public function actionSendActs()
    {
        $arSelected = Yii::$app->request->post('selection');
        if(!$arSelected)
            throw new InvalidParamException('Acts not set');

        $arActs = Acts::find()->where(['id' => $arSelected])->andWhere('sent is NULL OR sent = 0')->all();
        if(!$arActs)
            throw new NotFoundHttpException('Acts not found');

        $arCUser = [];
        /** @var Acts $act */
        foreach($arActs as $act)
            if(!in_array($act->cuser_id,$arCUser))
                $arCUser [] = $act->cuser_id;

        $arCUserEmail = CUser::getCUserEmails($arCUser); //получаем емаил пользователя
        $arCUserExtEmail = CUser::getCUserExtEmails($arCUser); //получаем емаил пользователя
        //$arSKUsers = CuserExternalAccount::getSKByCUserIDs($arCUser,CuserExternalAccount::TYPE_CSDA); // получаем внешние аккаунты
        if(!$arCUserEmail)
            throw new NotFoundHttpException('Contractor not found');

        $arReturnStatus = [
            'error' => [],
            'success' => []
        ];           //массив со статусами отправки сообщений по актам
        /** @var Acts $act */
        foreach($arActs as $act)
        {
            if(file_exists($act->getDocumentPath()) && isset($arCUserEmail[$act->cuser_id]) && !empty($arCUserEmail[$act->cuser_id]))
            {
                $arMsg = [
                    'iActId' => $act->id,
                    'toEmail' => $arCUserEmail[$act->cuser_id],
                    'toExtEmail' => $arCUserExtEmail[$act->cuser_id],
                    'iBUserId' => Yii::$app->user->id
                ];
                if(Yii::$app->rabbit->sendMessage(Rabbit::QUEUE_ACTS_SEND_LETTER,$arMsg))
                {
                    $arReturnStatus['success'][] = $act->id;
                }else{
                    $arReturnStatus['error'][] = $act->id;
                }
            }else{
                $arReturnStatus['error'][] = $act->id;
            }
        }

        Yii::$app->response->format = Response::FORMAT_JSON;    //set answer format
        return $arReturnStatus;
    }

    /**
     * @return array
     */
    public function actionCheckActNumber()
    {
        $iLegalPersonId = Yii::$app->request->post('iLegalId');
        $iNumber = Yii::$app->request->post('number');
        $date = Yii::$app->request->post('date');
        $year = date("Y",strtotime($date));
        if(empty($iLegalPersonId) || empty($iNumber))
            throw new InvalidParamException();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $exist = Acts::find()->where(['act_num' => $iNumber,'lp_id' => $iLegalPersonId])->andWhere(['>=','act_date', $year.'01-01'])->andWhere(['<=','act_date', $year.'12-31'])->exists();
        return ['answer' => !$exist];
    }

    /**
     * @return int
     */
    public function actionGetNextActNumber()
    {
        $iLegalPerson = Yii::$app->request->post('iLegalPerson');
        $date = Yii::$app->request->post('date');
        $year = date("Y",strtotime($date));
        if(empty($iLegalPerson))
            throw new InvalidParamException();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Acts::getNextActNumber($iLegalPerson,$year);
    }

    public function actionCheckContractorFields()
    {
        $iCUserId = Yii::$app->request->post('iCUserId');
        if(!$iCUserId)
            throw new InvalidParamException();
        /** @var CUser $obCUser */
        $obCUser = CUser::find()->joinWith('requisites')->where([CUser::tableName().'.id' => $iCUserId])->one();
        if(!$obCUser)
            throw new NotFoundHttpException();
        $error = '';
        $obRequisites = $obCUser->requisites;
        if(empty($obCUser->getInfo()))
        {
            $error.=Yii::t('app/book','Not filled Company name');
            $error.='<br>';
        }

        $error = $this->checkForError('site',$obRequisites,'Not filled Company site',$error);
        $error = $this->checkForError('ynp',$obRequisites,'Not filled Company ynp',$error);
        $error = $this->checkForError('ch_account',$obRequisites,'Not filled Company Ch Account',$error);
        $error = $this->checkForError('b_name',$obRequisites,'Not filled Company bank name',$error);
        $error = $this->checkForError('bank_address',$obRequisites,'Not filled Company bank address',$error);
        $error = $this->checkForError('b_code',$obRequisites,'Not filled Company bank code',$error);
        $error = $this->checkForError('j_address',$obRequisites,'Not filled Company j address',$error);
        $error = $this->checkForError('p_address',$obRequisites,'Not filled Company p address',$error);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'hasError' => !empty($error),
            'corpName' => $obCUser->getInfo(),
            'error' => $error
        ];

    }

    /**
     * @param $attribute
     * @param $obRequisites
     * @param $errorTest
     * @param $error
     * @return string
     */
    protected function checkForError($attribute,$obRequisites,$errorTest,$error)
    {
        if(!is_object($obRequisites) || (is_object($obRequisites) && (empty($obRequisites->$attribute) || $obRequisites->$attribute == '.')))
        {
            $error.=Yii::t('app/book',$errorTest);
            $error.='<br>';
        }
        return $error;
    }

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\base\ExitException
     */
    public function actionUpdateCuserEmail()
    {
        $pk = Yii::$app->request->post('pk');
        $value = Yii::$app->request->post('value');

        if(empty($pk) || empty($value))
            throw new InvalidParamException();

        $obCuser = CUser::find()->where([CUser::tableName().'.id' => $pk])->joinWith('requisites')->one();
        if(!$obCuser || !$obCuser->requisites)
            throw new NotFoundHttpException();

        $obRequisites = $obCuser->requisites;

        $obRequisites->c_email = $value;
        if(!$obRequisites->save())
            throw new ServerErrorHttpException();

        return Yii::$app->end(200);
    }
}
