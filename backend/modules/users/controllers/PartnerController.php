<?php

namespace backend\modules\users\controllers;

use backend\modules\users\form\ExternalCSDA;
use common\components\csda\CSDAPartner;
use common\models\managers\PartnerPurseManager;
use common\models\PartnerCuserServ;
use common\models\search\PartnerCuserServSearch;
use Yii;
use common\models\Partner;
use common\models\search\PartnerSearch;
use backend\components\AbstractBaseBackendController;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * PartnerController implements the CRUD actions for Partner model.
 */
class PartnerController extends AbstractBaseBackendController
{
    /**
     * Lists all Partner models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PartnerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Partner model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {

        $obForm = new ExternalCSDA();
        $obForm->partnerID = $id;
        return $this->render('view', [
            'model' => $this->findModel($id),
            'obForm' => $obForm
        ]);
    }

    /**
     * Creates a new Partner model and PartnerPurse model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Partner();
        if ($model->load(Yii::$app->request->post()) ) {
            $tr  = Yii::$app->db->beginTransaction();  //транзакция, так как работам с двумя моделями
            if($model->save() && PartnerPurseManager::createPurse($model->id)) //создаем партнера и его кашелек
                {
                    $tr->commit();
                    Yii::$app->session->setFlash('success',Yii::t('app/users','Partner and purse successfully created'));
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            $tr->rollBack();
            Yii::$app->session->setFlash('error',Yii::t('app/users','Error can not create partner or purse'));
        }
        return $this->render('create', [
                'model' => $model,
            ]);
    }

    /**
     * Updates an existing Partner model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Partner model.
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
     * Finds the Partner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Partner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Partner::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return string
     */
    public function actionLinkContractorService($id)
    {
        $searchModel = new PartnerCuserServSearch();
        $searchModel->partner_id = $id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('link_contractor_service',[
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'id' => $id
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionCreateCuserServ($id)
    {
        $model = new PartnerCuserServ(['partner_id' => $id,'connect' => date('Y-m-d')]);

        if($model->load(Yii::$app->request->post()) && $model->save())
        {
            Yii::$app->session->setFlash('success',Yii::t('app/users','Link successfully added'));
            return $this->redirect(['link-contractor-service','id' => $id]);
        }

        return $this->render('create-cuser-serv',[
            'model' => $model
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteLinkCuserServ($id)
    {
        $model = PartnerCuserServ::findOne($id);
        if(!$model)
            throw new NotFoundHttpException;
        $model->delete();
        return $this->redirect(['link-contractor-service','id' => $id]);
    }

    public function actionConnectCsda()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $arReturn = ['error' => '','data' => ''];
        $model = new ExternalCSDA();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                if($model->makeRequest())
                    $arReturn['data'] = '1';
                else
                    $arReturn['error'] = implode('</br>',$model->getSPErrors());
            }else{
                $arErr = [];
                foreach($model->getErrors() as $key => $value)
                {
                    $arErr [] = $model->getAttributeLabel($key).':'.implode(';',$value);
                }
                $arReturn['error'] = implode('</br>',$arErr);
            }
        }else{
            $arReturn['error'] = Yii::t('app/users','Can not get POST parameters');
        }


        return $arReturn;
    }

    public function actionDisconnectCsda()
    {
        $psk = Yii::$app->request->post('psk');
        $pid = Yii::$app->request->post('pid');


        if(empty($psk) || empty($pid))
            throw new InvalidParamException('psk and pid must be set');
        Yii::$app->response->format = Response::FORMAT_JSON;
        $obCSDA = new CSDAPartner();
        $res = $obCSDA->deleteUser($psk);
        if($res)
        {
            /** @var Partner $obPartner */
            $obPartner = Partner::findOneByIDCached($pid);
            $obPartner->psk = '';
            return $obPartner->save();
        }
        return false;
     }
}
