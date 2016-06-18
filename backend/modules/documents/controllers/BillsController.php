<?php

namespace backend\modules\documents\controllers;

use backend\modules\documents\form\BillForm;
use backend\widgets\Alert;
use common\components\helpers\CustomHelper;
use common\models\BillServices;
use common\models\BillTemplate;
use common\models\CuserServiceContract;
use common\models\LegalPerson;
use common\models\managers\BillsManager;
use Yii;
use common\models\Bills;
use common\models\search\BillsSearch;
use backend\components\AbstractBaseBackendController;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\models\CUser;
/**
 * BillsController implements the CRUD actions for Bills model.
 */
class BillsController extends AbstractBaseBackendController
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
                    'actions' => [
                        'index',
                        'view',
                        'update',
                        'create',
                        'get-bill',
                        'find-bill-template',
                        'get-bill-template-detail',
                        'bill-copy',
                        'find-docx-tpl',
                        'get-tpl-by-id',
                        'find-service-tpl'
                    ],
                    'allow' => true,
                    'roles' => ['bookkeeper']
                ],
                [
                    'allow' => true,
                    'roles' => ['admin','moder']
                ]
            ]
        ];
        $tmp['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'delete' =>                     ['post'],
                'find-bill-template' =>         ['post'],
                'get-bill-template-detail' =>   ['post'],
                'find-docx-tpl' => ['post'],
                'get-tpl-by-id' => ['post'],
                'find-service-tpl' => ['post']
            ],
        ];

        return $tmp;
    }

    /**
     * Lists all Bills models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BillsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $iTotal = $searchModel->countTotal(Yii::$app->request->queryParams);
        $cuserDesc = !empty($searchModel->cuser_id) ? CUser::getCuserInfoById($searchModel->cuser_id) : $searchModel->cuser_id;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'iTotal' => $iTotal,
            'cuserDesc' => $cuserDesc
        ]);
    }

    /**
     * Displays a single Bills model.
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
     * Creates a new Bills model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BillForm();
        $model->sBayTarget = Yii::t('app/documents','DefaultBuytarget');
        if($model->load(Yii::$app->request->post()))
        {
            if($model->makeRequest())
            {
                return $this->redirect(['index']);
            }
        }
        
        $cuserDesc = '';
        if($model->iCuserId)
            $cuserDesc = CUser::getCuserInfoById($model->iCuserId);
        
        return $this->render('create',[
            'model' => $model,
            'cuserDesc' => $cuserDesc
        ]);
    }

    /**
     * Updates an existing Bills model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->service_id)
        {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                    'view_form' => '_form',
                    'cuserDesc' => '',
                    'arServices' => []
                ]);
            }
        }else{

            $modelForm = new BillForm();
            
            $modelForm->iCuserId = $model->cuser_id;
            $modelForm->iLegalPerson = $model->l_person_id;
            $modelForm->iDocxTpl = $model->docx_tmpl_id;
            $modelForm->fAmount = $model->amount;
            $modelForm->bUseTax = $model->use_vat;
            $modelForm->bTaxRate = $model->vat_rate;
            $modelForm->sBayTarget = $model->buy_target;
            $modelForm->sOfferContract = $model->offer_contract;
            $modelForm->sDescription = $model->description;

            $arServices = BillServices::find()->where(['bill_id' => $model->id])->with('service')->all();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {

                $cuserDesc = '';
                if($modelForm->iCuserId)
                    $cuserDesc = CUser::getCuserInfoById($modelForm->iCuserId);
                
                return $this->render('update', [
                    'billModel' => $model,
                    'model' => $modelForm,
                    'view_form' => '_form_refactoring',
                    'cuserDesc' => $cuserDesc,
                    'arServices' => $arServices
                ]);
            }
        }
    }

    /**
     * Deletes an existing Bills model.
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
     * Finds the Bills model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Bills the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Bills::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionFindBillTemplate()
    {
        $iServID = Yii::$app->request->post('iServID');
        $lPID = Yii::$app->request->post('lPID');
        $iCntr = Yii::$app->request->post('iCntr');

        if(empty($iServID) || empty($lPID))
            throw new InvalidParamException();
        /** @var BillTemplate $model */
        $model = BillTemplate::find()->where([ 'l_person_id' => $lPID,'service_id' => $iServID])->one();

        if(!empty($model))
        {
            /** @var CuserServiceContract $obServ */
            $obServ = CuserServiceContract::findOne(['cuser_id' => $iCntr,'service_id' => $iServID]);
            if($obServ)
                $model->offer_contract = '№'.$obServ->cont_number.' от '.$obServ->cont_date;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return empty($model) ? '' : $model;
    }

    /**
     * @return mixed|null|string|static
     * @throws \yii\base\InvalidParamException
     */
    public function actionGetBillTemplateDetail()
    {
        $iBTpl = Yii::$app->request->post('iBTpl');
        $iCntr = Yii::$app->request->post('iCntr');
        if(empty($iBTpl))
            throw new InvalidParamException();

        $model = BillTemplate::findOneByIDCached($iBTpl);

        if(!empty($model))
        {
            /** @var CuserServiceContract $obServ */
            $obServ = CuserServiceContract::findOne(['cuser_id' => $iCntr,'service_id' => $model->service_id]);
            if($obServ)
                $model->offer_contract = '№ '.$obServ->cont_number.' от '.$obServ->cont_date;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return empty($model) ? '' : $model;
    }

    /**
     * @param $id
     * @param $type
     */
    public function actionGetBill($id,$type)
    {
        /** @var BillsManager $model */
        $model = BillsManager::findOne($id);
        $model->getDocument($type);
        Yii::$app->end();
    }

    /**
     * @return int
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionFindLegalPerson()
    {
        $lPID = Yii::$app->request->post('lPID');
        $model = LegalPerson::findOne($lPID);
        if(empty($model))
            throw new NotFoundHttpException('Legal person not found');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'use_vat' => $model->use_vat,
            'docx_id' => $model->docx_id,
            'id' => $model->id
        ];
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionBillCopy($id)
    {
        /** @var Bills $obBills */
        $obBills = Bills::findOne($id);
        if(!$obBills)
            throw new NotFoundHttpException('Bill not found');

        $obBills->isNewRecord = TRUE;
        $obBills->id = NULL;
        $obBills->updateForCopy();
        if(!$obBills->save()) {
           Yii::$app->session->setFlash(Alert::TYPE_SUCCESS,Yii::t('app/crm','Bill successfully copied'));
        }else
        {
            Yii::$app->session->setFlash(Alert::TYPE_ERROR,Yii::t('app/crm','Error. Can not copy bill'));
        }

        return $this->redirect(['index']);
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionFindDocxTpl()
    {
        $iLegalId = Yii::$app->request->post('iLegalId');
        if(!$iLegalId)
            throw new InvalidParamException;

        $obLegalPerson = LegalPerson::find()->select(['id','docx_id','use_vat'])->where(['id' => $iLegalId])->one();

        if(!$obLegalPerson)
            throw new NotFoundHttpException();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'id' => $obLegalPerson->id,
            'docx_id' => $obLegalPerson->docx_id,
            'use_vate' => $obLegalPerson->use_vat,
            'vat_rate' => CustomHelper::getVat()
        ];
    }

    /**
     * @return array|string
     */
    public function actionFindServiceTpl()
    {
        $iLegalId = Yii::$app->request->post('iLegalId');
        $iCtrId = Yii::$app->request->post('iCtrId');
        $arServices = Yii::$app->request->post('arServ');

        if(!$iLegalId || !$iCtrId || empty($arServices))
        {
            throw new InvalidParamException;
        }

        /** @var BillTemplate $model */
        $models = BillTemplate::find()->where([ 'l_person_id' => $iLegalId,'service_id' => $arServices])->all();
        $models = ArrayHelper::index($models,'service_id');
        if(!empty($models))
        {
            $obServices = CuserServiceContract::find()->where(['cuser_id' => $iCtrId,'service_id' => $arServices])->all();
            $obServices = ArrayHelper::index($obServices,'service_id');

            foreach ($models as $key => &$value)
            {
                if(isset($obServices[$key]))
                {
                    $obTmp = $obServices[$key];
                    $value->offer_contract = '№'.$obTmp->cont_number.' от '.$obTmp->cont_date;
                }
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return empty($models) ? '' : $models;
    }

    public function actionGetTplById()
    {

    }

    /*
        $iBTpl = Yii::$app->request->post('iBTpl');
        $iCntr = Yii::$app->request->post('iCntr');
        if(empty($iBTpl))
            throw new InvalidParamException();

        $model = BillTemplate::findOneByIDCached($iBTpl);

        if(!empty($model))
        {

            $obServ = CuserServiceContract::findOne(['cuser_id' => $iCntr,'service_id' => $model->service_id]);
            if($obServ)
                $model->offer_contract = '№ '.$obServ->cont_number.' от '.$obServ->cont_date;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return empty($model) ? '' : $model;
        */

}
