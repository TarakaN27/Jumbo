<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.4.16
 * Time: 17.14
 */

namespace backend\modules\partners\controllers;


use backend\components\AbstractBaseBackendController;
use backend\modules\partners\models\PartnerAllowForm;
use backend\widgets\Alert;
use common\models\AbstractActiveRecord;
use common\models\CUser;
use common\models\PartnerAllowService;
use common\models\PartnerCuserServ;

use common\models\search\CUserSearch;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\AbstractModel;
use Exception;
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
        $searchModel = new CUserSearch();
        $dataProvider = $searchModel->searchPartners(Yii::$app->request->queryParams);

        return $this->render('index',[
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    /***
     * @param $pid
     * @return string
     */
    public function actionLinkLead($pid)
    {
        $query = PartnerCuserServ::find()->where(['partner_id' => $pid]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $this->render('link-lead',[
            'dataProvider' => $dataProvider,
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
        return $this->redirect(['link-lead','pid' => $id]);
    }

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

}