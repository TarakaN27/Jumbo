<?php

namespace backend\modules\bonus\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use backend\modules\bonus\form\ConnectBonusToCuserForm;
use backend\modules\bonus\form\ConnectBonusToUserForm;
use backend\modules\bonus\form\ExceptBonusSchemeCUser;
use common\models\BonusSchemeRecords;
use common\models\BonusSchemeService;
use common\models\BonusSchemeServiceHistory;
use common\models\CUser;
use common\models\LegalPerson;
use Yii;
use common\models\BonusScheme;
use common\models\search\BonusSchemeSearch;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use common\models\Services;
use yii\web\ServerErrorHttpException;

/**
 * DefaultController implements the CRUD actions for BonusScheme model.
 */
class DefaultController extends AbstractBaseBackendController
{
    /**
     * Lists all BonusScheme models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BonusSchemeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BonusScheme model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $arServices = Services::getServicesMap();
        $arLegal = [];
        if (in_array($model->type, [$model::TYPE_SIMPLE_BONUS, $model::TYPE_COMPLEX_TYPE]))
            $arLegal = LegalPerson::getLegalPersonMap();

        $arBServices = $model->services;

        $arUsers = $model->users;
        $arCusers = $model->cusers;

        return $this->render('view', [
            'model' => $model,
            'arServices' => $arServices,
            'arLegal' => $arLegal,
            'arBServices' => $arBServices,
            'arUsers' => $arUsers,
            'arCusers' => $arCusers
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws ServerErrorHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new BonusScheme();
        $arRates = [];
        if ($model->load(Yii::$app->request->post())) {
            $tr = Yii::$app->db->beginTransaction();
            $arServices = Services::getAllServices();
            $arRates = Yii::$app->request->post('records',[]);
            if ($model->save())  //сохраняем схему
            {

                if ($model->type != BonusScheme::TYPE_PAYMENT_RECORDS) {
                    $cost = Yii::$app->request->post('costs', []);
                    $multiple = Yii::$app->request->post('multiple', []);
                    $legal = Yii::$app->request->post('legal', []);
                    $monthPersent = Yii::$app->request->post('months', []);
                    $simplePercent = Yii::$app->request->post('simple_percent', []);
                    foreach ($arServices as $obServ)     //сохраняем услуги по схеме
                    {
                        $obBSev = new BonusSchemeService([
                            'scheme_id' => $model->id,
                            'service_id' => $obServ->id,
                            'month_percent' => isset($monthPersent[$obServ->id]) ? $monthPersent[$obServ->id] : [],
                            'legal_person' => isset($legal[$obServ->id]) ? $legal[$obServ->id] : [],
                            'cost' => isset($cost[$obServ->id]) ? $cost[$obServ->id] : NULL,
                            'unit_multiple' => isset($multiple[$obServ->id]) ? 1 : NULL,
                            'simple_percent' => isset($simplePercent[$obServ->id]) ? $simplePercent[$obServ->id] : NULL
                        ]);

                        if (!$obBSev->save()) {
                            $tr->rollBack();
                            throw new ServerErrorHttpException();
                        }
                    }
                } else {
                    if (count($arRates) === 0) {
                        $tr->rollBack();
                        throw new InvalidParamException();
                    }
                    
                    $obRecords = new BonusSchemeRecords([
                        'scheme_id' => $model->id,
                        'params' => $arRates
                    ]);

                    if (!$obRecords->save()) {
                        $tr->rollBack();
                        throw new ServerErrorHttpException();
                    }
                }

                $tr->commit();
                return $this->redirect(['view', 'id' => $model->id]);
            }
            $tr->rollBack();
        }
        return $this->render('create', [
            'model' => $model,
            'arBServices' => [],
            'arRates' => $arRates
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $arBServicesTmp = $model->services;
        $arBServices = [];
        foreach ($arBServicesTmp as $value)
            $arBServices[$value->service_id] = $value;
        $arRates = [];
        if($model->type == BonusScheme::TYPE_PAYMENT_RECORDS)
        {
            $obRate = $model->schemeRecords;
            if($obRate)
            {
                $arRates = $obRate->params;
            }else{
                $obRate = new BonusSchemeRecords(['scheme_id' => $model->id,'params' => []]);
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            $tr = Yii::$app->db->beginTransaction();
            $arServices = Services::getAllServices();
            $arRates = Yii::$app->request->post('records',[]);
            if ($model->save())  //сохраняем схему
            {

                if ($model->type != BonusScheme::TYPE_PAYMENT_RECORDS) {

                    $cost = Yii::$app->request->post('costs', []);
                    $multiple = Yii::$app->request->post('multiple', []);
                    $legal = Yii::$app->request->post('legal', []);
                    $monthPersent = Yii::$app->request->post('months', []);
                    $simplePercent = Yii::$app->request->post('simple_percent', []);
                    $model->unlinkAll('services', TRUE); //удаляем старые услуги

                    $rows = [];
                    foreach ($arBServicesTmp as $item) {
                        $rows [] = [
                            '',
                            $model->id,
                            $item->service_id,
                            Json::encode($item->month_percent),
                            $item->cost,
                            $item->unit_multiple,
                            time(),
                            time(),
                            Json::encode($item->legal_person),
                            $item->simple_percent
                        ];
                    }
                    $historyModel = new BonusSchemeServiceHistory();    //пишем историю
                    if (!Yii::$app->db->createCommand()
                        ->batchInsert(BonusSchemeServiceHistory::tableName(), $historyModel->attributes(), $rows)
                        ->execute()
                    ) {
                        $tr->rollBack();
                        throw new ServerErrorHttpException();
                    }

                    foreach ($arServices as $obServ)     //сохраняем услуги по схеме
                    {
                        $obBSev = new BonusSchemeService([
                            'scheme_id' => $model->id,
                            'service_id' => $obServ->id,
                            'month_percent' => isset($monthPersent[$obServ->id]) ? $monthPersent[$obServ->id] : [],
                            'legal_person' => isset($legal[$obServ->id]) ? $legal[$obServ->id] : [],
                            'cost' => isset($cost[$obServ->id]) ? $cost[$obServ->id] : NULL,
                            'unit_multiple' => isset($multiple[$obServ->id]) ? 1 : NULL,
                            'simple_percent' => isset($simplePercent[$obServ->id]) ? $simplePercent[$obServ->id] : NULL
                        ]);

                        if (!$obBSev->save()) {
                            $tr->rollBack();
                            throw new ServerErrorHttpException();
                        }
                    }

                }else{
                    $obRate->params = $arRates;
                    if (!$obRate->save()) {
                        $tr->rollBack();
                        throw new ServerErrorHttpException();
                    }
                }
                
                $tr->commit();
                return $this->redirect(['view', 'id' => $model->id]);
            }
            $tr->rollBack();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'arBServices' => $arBServices,
            'arRates' => $arRates
        ]);
    }

    /**
     * Deletes an existing BonusScheme model.
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
     * Finds the BonusScheme model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BonusScheme the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BonusScheme::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionConnectUser($id)
    {
        $model = $this->findModel($id);
        $obForm = new ConnectBonusToUserForm(['obScheme' => $model]);
        if ($obForm->load(Yii::$app->request->post())) {
            if ($obForm->makeRequest()) {
                $model->trigger(BonusScheme::EVENT_AFTER_UPDATE);
                Yii::$app->session->setFlash('success', Yii::t('app/bonus', 'User successfully connected'));
                return $this->redirect('index');
            }
        }
        $data = [];
        if (!empty($obForm->users))
            $data = ArrayHelper::map(
                BUser::find()->select(['id', 'fname', 'lname', 'mname'])->where(['id' => $obForm->users])->all(),
                'id', 'fio');
        return $this->render('connect_user', [
            'model' => $model,
            'obForm' => $obForm,
            'data' => $data
        ]);
    }

    public function actionConnectCuser($id)
    {
        $model = $this->findModel($id);
        $obForm = new ConnectBonusToCuserForm(['obScheme' => $model]);
        if ($obForm->load(Yii::$app->request->post())) {
            if ($obForm->makeRequest()) {
                $model->trigger(BonusScheme::EVENT_AFTER_UPDATE);
                Yii::$app->session->setFlash('success', Yii::t('app/bonus', 'Cuser successfully connected'));
                return $this->redirect('index');
            }
        }
        $data = [];
        if (!empty($obForm->users))
            $data = ArrayHelper::map(
                CUser::find()
                    ->alias('c')
                    ->select(['c.id', 'c.requisites_id', 'r.j_lname', 'r.j_mname', 'r.j_fname', 'r.type_id'])
                    ->joinWith('requisites r')
                    ->where(['c.id' => $obForm->users])->all(),
                'id', 'infoWithSite');
        return $this->render('connect_cuser', [
            'model' => $model,
            'obForm' => $obForm,
            'data' => $data
        ]);

    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionExceptUser($id)
    {
        /** @var BonusScheme $model */
        $model = $this->findModel($id);
        $obForm = new ExceptBonusSchemeCUser(['obScheme' => $model]);
        if ($obForm->load(Yii::$app->request->post())) {
            if ($obForm->makeRequest()) {
                $model->trigger(BonusScheme::EVENT_AFTER_UPDATE);
                Yii::$app->session->setFlash('success', Yii::t('app/bonus', 'Cuser add to except'));
                return $this->redirect('index');
            }
        }
        $data = [];
        if (!empty($obForm->users))
            $data = ArrayHelper::map(
                CUser::find()
                    ->alias('c')
                    ->select(['c.id', 'c.requisites_id', 'r.j_lname', 'r.j_mname', 'r.j_fname', 'r.type_id'])
                    ->joinWith('requisites r')
                    ->where(['c.id' => $obForm->users])->all(),
                'id', 'infoWithSite');
        return $this->render('except_cuser', [
            'model' => $model,
            'obForm' => $obForm,
            'data' => $data
        ]);

    }
}
