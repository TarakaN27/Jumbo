<?php

namespace backend\modules\users\controllers;

use backend\components\AbstractBaseBackendController;
use backend\modules\users\models\CreateExternalAccount;
use common\components\csda\CSDAConnector;
use common\models\CuserExternalAccount;
use common\models\CuserPreferPayCond;
use common\models\CUserRequisites;
use common\models\CuserServiceContract;
use common\models\Services;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Yii;
use common\models\CUser;
use common\models\search\CUserSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * ContractorController implements the CRUD actions for CUser model.
 */
class ContractorController extends AbstractBaseBackendController
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
                    'actions' => ['services-contract'],
                    'roles' => ['moder','sale','jurist','bookkeeper','admin']
                ],
                [
                    'allow' => true,
                    'roles' => ['moder','sale','bookkeeper','admin']
                ]
            ]
        ];
        return $tmp;
    }


    /**
     * Lists all CUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CUser model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $modelR = $model->requisites;
        return $this->render('view', [
            'model' => $model,
            'modelR' => $modelR
        ]);
    }

    /**
     * Creates a new CUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CUser();
        $model->setDummyFields(); //@todo утановлены заглушки на имя пользователя и емаил. При необходимости убрать!
        $modelR = new CUserRequisites();
        if ($model->load(Yii::$app->request->post()) && $modelR->load(Yii::$app->request->post())) {

            if($model->is_resident != CUser::RESIDENT_YES)
                $modelR->isResident = FALSE;

            if($model->validate() && $modelR->validate())
            {
                $transaction = Yii::$app->db->beginTransaction(); //транзакция для того чтобы при ошибках сохранения не создавалось лишних записей
                try{
                    if($modelR->save() && $model->save())
                    {
                        $model->link('requisites',$modelR);
                        $transaction->commit();
                        Yii::$app->session->set('success',Yii::t('app/users','Contractor_successfully_added'));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }else{
                       $transaction->rollBack();
                    }
                }catch (\Exception $e)
                {
                    $transaction->rollBack();
                    Yii::$app->session->set('error',$e->getMessage());
                }
            }else{
                Yii::$app->session->set('error',Yii::t('app/users','Contractor_validate_error'));
            }
        }

        if(empty($modelR->type_id))
            $modelR->type_id = CUserRequisites::TYPE_F_PERSON;


        return $this->render('create', [
                'model' => $model,
                'modelR' => $modelR
            ]);

    }

    /**
     * Updates an existing CUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $modelR = $model->requisites;
        if(empty($modelR))
            $modelR = new CUserRequisites();

        if ($model->load(Yii::$app->request->post()) && $modelR->load(Yii::$app->request->post())) {

            if($model->is_resident != CUser::RESIDENT_YES)
                $modelR->isResident = FALSE;

            if($model->validate() && $modelR->validate())
            {
                $transaction = Yii::$app->db->beginTransaction(); //транзакция для того чтобы при ошибках сохранения не создавалось лишних записей
                try{
                    if($modelR->save() && $model->save())
                    {
                        $model->link('requisites',$modelR);
                        $transaction->commit();
                        Yii::$app->session->set('success',Yii::t('app/users','Contractor_successfully_updated'));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }else{
                        $transaction->rollBack();
                    }
                }catch (\Exception $e)
                {
                    $transaction->rollBack();
                    Yii::$app->session->set('error',$e->getMessage());
                }
            }else{
                Yii::$app->session->set('error',Yii::t('app/users','Contractor_validate_error'));
            }
        }

        if(empty($modelR->type_id))
            $modelR->type_id = CUserRequisites::TYPE_F_PERSON;

        return $this->render('update', [
                'model' => $model,
                'modelR' => $modelR
            ]);

    }

    /**
     * Deletes an existing CUser model.
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
     * Finds the CUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionPreferCond($id)
    {
        $models = CuserPreferPayCond::find()->where([
            'cuser_id' => $id
        ])->all();

        $arModels = [];
        foreach($models as $item)
        {
            $arModels[$item->service_id] = $item->cond_id;
        }

        $services = Services::getServicesMap();
        $arSelected = [];
        foreach($services as $key => $serv)
        {
            $arSelected[$key] = isset($arModels[$key]) ? $arModels[$key] : NULL;
        }

        $arPostServ = Yii::$app->request->post('service');
        if(!empty($arPostServ))
        {
            $trans = Yii::$app->db->beginTransaction();
            $bError = false;
            CuserPreferPayCond::deleteAll(['cuser_id' => $id]);
            foreach($arPostServ as $key=>$cond)
            {
                if(!$cond)
                    continue;
                $obCUPPC = new CuserPreferPayCond();
                $obCUPPC->cond_id = (int)$cond;
                $obCUPPC->service_id = (int)$key;
                $obCUPPC->cuser_id = $id;
                if(!$obCUPPC->save())
                {
                    $bError = TRUE;
                    break;
                }
                unset($obCUPPC);
            }

            if(!$bError)
            {
                $trans->commit();
                Yii::$app->session->setFlash('success','Изменения успешно сохранены');
                return $this->redirect(['/crm/company/index']);
            }else{

                Yii::$app->session->setFlash('error','Ошибка');
                $trans->rollBack();
            }
        }
        return $this->render('prefer_cond',[
            'models' => $models,
            'services' => $services,
            'arSelected' =>  $arSelected
        ]);
    }

    public function actionExternalAccount($iCID)
    {
        $csdaAcc = CuserExternalAccount::getExtAccForUser($iCID,CuserExternalAccount::TYPE_CSDA);
        return $this->render('external_acccount',[
            'csdaAcc' => $csdaAcc,
            'iCID' => $iCID
        ]);
    }


    public function actionCreateExternalAccount($iCID,$type)
    {
        /**
         * Необходимо проверить заданы ли условия для всех услуг.
         * если не заданы, то запрещаем создавать внешний аккаунт.
         * если заданы, то продолжаем создание аккаунта.
         */
        $serviceMap = Services::getServicesMap();
        $preferCond = CuserPreferPayCond::find()->select(['service_id'])->where([
            'cuser_id' => $iCID
        ])->all();

        $arCond = [];
        foreach($preferCond as $cond)
            $arCond [] = $cond->service_id;
        unset($preferCond);

        $allow = TRUE;
        /** @var Services $service */
        foreach($serviceMap as $key=>$service)
        {
            if(!in_array($key,$arCond))
            {
                $allow = FALSE;
                break;
            }
        }
        unset($serviceMap);

        $obCReq = CUserRequisites::find()
            ->select('c_email')
            ->leftJoin(CUser::tableName().' cu ','cu.requisites_id = '.CUserRequisites::tableName().'.id')
            ->where(['cu.id' => $iCID])
            ->one();


        $obModel = new CreateExternalAccount();
        $obModel->type = $type;
        if(!empty($obCReq))
            $obModel->email = $obCReq->c_email;
        $obModel->cUserID = $iCID;

        if($obModel->load(Yii::$app->request->post()) && $obModel->validate())
        {
            if($obModel->makeRequest())
            {
                Yii::$app->session->setFlash('success',Yii::t('app/users','External account successfully created'));
                return $this->redirect(['external-account','iCID' => $iCID]);
            }else{
                Yii::$app->session->setFlash('error',Yii::t('app/users','External account can not be created'));
            }
        }

        return $this->render('create_external_account',[
            'allow' => $allow,
            'type' => $type,
            'obModel' => $obModel
        ]);
    }

    /**
     * @param $iCID
     * @param $iExtID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDeleteExternalAccount($iCID,$iExtID,$type)
    {
        /** @var CuserExternalAccount $obExt */
        $obExt = CuserExternalAccount::findOne($iExtID);
        if(empty($obExt))
            throw new NotFoundHttpException();
        switch($type)
        {
            case CuserExternalAccount::TYPE_CSDA:
                $obCsda = new CSDAConnector();
                if($obCsda->deleteUser($obExt->secret_key))
                {
                    $obExt->delete();
                    Yii::$app->session->setFlash('success',Yii::t('app/users','External account successfully deleted'));
                    return $this->redirect(['external-account','iCID' => $iCID]);
                }

                Yii::$app->session->setFlash('error',Yii::t('app/users','External account can not be deleted'));
                return $this->redirect(['external-account','iCID' => $iCID]);
                break;

            default:
                break;
        }
        throw new InvalidArgumentException('Invalid type');
    }

    /**
     * @param $iCID
     * @return array
     */
    public function actionServicesContract($iCID)
    {
        $arService  = Services::getServicesMap();
        $arCSCTmp= CuserServiceContract::find()->where(['cuser_id' => $iCID])->all();

        $arCSC = [];
        foreach($arCSCTmp as $csc)
            $arCSC[$csc->service_id] = $csc;

        $arNumber = Yii::$app->request->post('number'); //номера договоров
        $arDate = Yii::$app->request->post('date'); //даты договров

        if(is_array($arNumber) && is_array($arDate))
        {
            $trans = Yii::$app->db->beginTransaction(); // начинаем транзакцию, так как редактируем много записей
            $bError = false;
            CuserServiceContract::deleteAll(['cuser_id' => $iCID]); //удаляем всме предыдущие настройки
            foreach($arNumber as $key=>$num)
            {
                /** @var ServiceDefaultContract $obSDC */
                $obSDC = new CuserServiceContract();
                $obSDC->cuser_id = $iCID;
                $obSDC->service_id = $key;
                $obSDC->cont_number = $num;
                $obSDC->cont_date = isset($arDate[$key]) ? $arDate[$key] : NULL;
                if(!$obSDC->save())
                {
                    $bError = TRUE;
                    break;
                }
                unset($obSDC);
            }

            if(!$bError)
            {
                $trans->commit();
                Yii::$app->session->setFlash('success','Изменения успешно сохранены');
                return $this->redirect(['/crm/company/index']);
            }else{

                Yii::$app->session->setFlash('error','Ошибка');
                $trans->rollBack();
            }
        }

        return $this->render('service_contract',[
            'arService' => $arService,
            'arCSC' => $arCSC
        ]);
    }
}
