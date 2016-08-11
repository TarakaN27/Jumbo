<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.4.16
 * Time: 17.14
 */

namespace backend\modules\partners\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\partners\models\Partner;
use backend\modules\partners\models\PartnerAllowForm;
use backend\modules\partners\models\PartnerDetailLeadsForm;
use backend\modules\partners\models\PartnerLinkLead;
use backend\modules\partners\models\PartnerMultiLInkForm;
use backend\widgets\Alert;
use common\models\AbstractActiveRecord;
use common\models\CUser;
use common\models\PartnerAllowService;
use common\models\PartnerCuserServ;

use common\models\search\CUserSearch;
use common\models\Services;
use Yii;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use common\models\AbstractModel;
use Exception;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;

class PartnersController extends AbstractBaseBackendController
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
                    'roles' => ['admin','partner_manager']
                ]
            ]
        ];
        return $tmp;
    }


    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new Partner();
        $dataProvider = $searchModel->searchPartners(Yii::$app->request->queryParams);
        $total = $searchModel->getTotalSum($dataProvider->query);

        return $this->render('index',[
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'total' => $total,
        ]);
    }

    /***
     * @param $pid
     * @return string
     */
    public function actionLinkLead($pid)
    {
        $searchModel = new PartnerLinkLead();
        $dataProvider = $searchModel->searchLinkLead(Yii::$app->request->queryParams, $pid);

        return $this->render('link-lead',[
            'dataProvider' => $dataProvider,
            'searchModel'=>$searchModel,
            'pid' => $pid
        ]);
    }

    /**
     * @param $pid
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionAddLink($pid)
    {
        $models =  [new PartnerCuserServ(['partner_id' => $pid])];
        $select2Data = [];

        $arServMap = PartnerAllowService::getServicesMapForPartner($pid);   //get allow service for partner

        if(Yii::$app->request->post('PartnerCuserServ'))
        {
            $models = AbstractModel::createMultiple(PartnerCuserServ::classname());
            foreach ($models as &$mod)
                $mod->partner_id = $pid;
            AbstractModel::loadMultiple($models,Yii::$app->request->post());
            $valid = AbstractModel::validateMultiple($models);
            if($valid)
            {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $bFlag = TRUE;
                    foreach ($models as $model)
                        if(!$model->save())
                        {
                            $bFlag = FALSE;
                            break;
                        }
                    if($bFlag)
                    {
                        $transaction->commit();
                        return $this->redirect(['link-lead','pid' => $pid]);
                    }else{
                        $transaction->rollBack();
                    }
                }catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
            $arCUserID = [];
            foreach ($models as $model)
                if(!empty($model->cuser_id) && !in_array($model->cuser_id,$arCUserID))
                    $arCUserID [] = $model->cuser_id;
            if($arCUserID)
                $select2Data = ArrayHelper::map(
                    CUser::find()->where(['id' => $arCUserID])->with('requisites')->all(),
                    'id',
                    'infoWithSite'
                );
        }
        return $this->render('add-link',[
            'models' => $models,
            'select2Data' => $select2Data,
            'pid' => $pid,
            'arServMap' => $arServMap
        ]);
    }

    /**
     * Арживация и восстановление связи партнер -- лид
     * @return int
     * @throws NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionArchive()
    {
        $pk = Yii::$app->request->post('pk');
        $date = Yii::$app->request->post('date');
        $currentVal = Yii::$app->request->post('val');
        /** @var  PartnerCuserServ $model */
        $model = PartnerCuserServ::find()->where(['id' => $pk])->one();
        if(!$model)
            throw new NotFoundHttpException('Model not found');

        Yii::$app->response->format = Response::FORMAT_JSON;
        if($model->archive == $currentVal)
        {
            $model->archiveDate = $date;
            return $model->archive();
        }

        return $model->archive;
    }

    /***
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = PartnerCuserServ::find()->where(['id' => $id])->one();
        if(!$model)
            throw new NotFoundHttpException('Model not found');
        $model->delete();
        return $this->redirect(['link-lead','pid' => $model->partner_id]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws InvalidParamException
     */
    public function actionAllowServices($id)
    {
        $obPartner = CUser::find()->where(['id' => $id,'partner' => AbstractActiveRecord::YES])->one();
        if(!$obPartner)
            throw new NotFoundHttpException('Partner not found');
        $model = new PartnerAllowForm(['iPartnerID' => $obPartner->id]);

        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->makeRequest())
            {
                Yii::$app->session->set(Alert::TYPE_SUCCESS,Yii::t('app/users','Service successfully added to allow list'));
                return $this->redirect(['index']);
            }else{
                Yii::$app->session->set(Alert::TYPE_ERROR,Yii::t('app/users','Service successfully added to allow list'));
                    
            }
        }
        return $this->render('allow_services',[
            'obPartner' => $obPartner,
            'model' => $model
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws InvalidParamException
     */
    public function actionView($id)
    {
        $obPartner = CUser::find()->with('requisites','manager','partnerManager')->where(['id' => $id,'partner' => AbstractActiveRecord::YES])->one();
        if(!$obPartner)
            throw new NotFoundHttpException('Partner not found');
        
        $obPurse = $obPartner->partnerPurse;

        $arLeadsProvider = new ActiveDataProvider([
            'query' => PartnerCuserServ::find()->with('partner','cuser','service')->where(['partner_id' => $id])->orderBy(['cuser_id'=>SORT_DESC, 'connect'=>'DESC']),
            'pagination' => [
                'pageSize' => -1,
            ],
        ]);
        return $this->render('view',[
            'obPartner' => $obPartner,
            'arLeads' => $arLeadsProvider->getModels(),
            'pid' => $id,
            'obPurse' => $obPurse
        ]);
    }

    /**
     * @param $pid
     * @return string
     * @throws NotFoundHttpException
     * @throws InvalidParamException
     */
    public function actionViewLeadDetail($pid)
    {
        $obPartner = CUser::find()->where(['id' => $pid,'partner' => AbstractActiveRecord::YES])->one();
        if(!$obPartner)
            throw new NotFoundHttpException('Partner not found');
        
        $model = new PartnerDetailLeadsForm(['obPartner' => $obPartner]);

        $data = [];
        $data = $model->makeRequest();

        return $this->render('view-lead-detail',[
            'model' => $model,
            'data' => $data
        ]);
    }

    /**
     * @return string
     */
    public function actionGetMultiLinkForm()
    {
        $pk = Yii::$app->request->post('pid');
        $arAllowSerID = PartnerAllowService::find()->where(['cuser_id' => $pk])->all();
        $arServices = [];
        if($arAllowSerID)
        {
            $arASIds = ArrayHelper::getColumn($arAllowSerID,'service_id');
            $arServObj = Services::find()->select(['id','name'])->where(['id' => $arASIds])->all();
            $arServices = ArrayHelper::map($arServObj,'id','name');
        }
        $model = new PartnerMultiLInkForm();
        $model->date = Yii::$app->formatter->asDate('NOW');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->renderAjax('parts/_get_multi_link_form',[
            'arServ' => $arServices,
            'model' => $model
        ]);
    }

}