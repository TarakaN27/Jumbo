<?php

namespace backend\modules\partners\controllers;

use backend\components\AbstractBaseBackendController;
use common\components\helpers\CustomHelperMoney;
use common\models\LegalPerson;
use common\models\PartnerSchemesServices;
use common\models\PartnerSchemesServicesHistory;
use common\models\Services;
use Yii;
use common\models\PartnerSchemes;
use common\models\search\PartnerSchemesSearch;
use yii\base\InvalidParamException;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;


/**
 * PartnerSchemesController implements the CRUD actions for PartnerSchemes model.
 */
class PartnerSchemesController extends AbstractBaseBackendController
{
    /**
     * Lists all PartnerSchemes models.
     * @return mixed
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        $searchModel = new PartnerSchemesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PartnerSchemes model.
     * @param integer $id
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $modelDetail = $model->partnerSchemesServices;
        $arLegal = LegalPerson::getLegalPersonMap();
        $arServices = Services::getServicesMap();

        return $this->render('view', [
            'model' => $model,
            'modelDetail' => $modelDetail,
            'arLegal' => $arLegal,
            'arServices' => $arServices
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws ServerErrorHttpException
     * @throws \yii\db\Exception
     * @throws InvalidParamException
     */
    public function actionCreate()
    {
        $model = new PartnerSchemes();
        $arServices = Services::getServicesMap();
        $arLP = \common\models\LegalPerson::getLegalPersonMap();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $transaction = Yii::$app->db->beginTransaction();
            if($model->save())
            {
                $legal = Yii::$app->request->post('legal',[]);
                $ranges = Yii::$app->request->post('range',[]);
                $group = Yii::$app->request->post('group',[]);

                if(!empty($ranges))
                    foreach ($ranges as &$rangeItem)
                        if(!empty($rangeItem))
                            foreach ($rangeItem as &$item)
                            {
                                if(isset($item['left'],$item['right'],$item['percent']))
                                {
                                    $item['left'] = CustomHelperMoney::convertNumberToValid($item['left']);
                                    $item['right'] = CustomHelperMoney::convertNumberToValid($item['right']);
                                    $item['percent'] = CustomHelperMoney::convertNumberToValid($item['percent']);
                                }
                            }

                foreach ($arServices as $serviceID => $serviceName)
                {
                    $obSchemeServ = new PartnerSchemesServices([
                        'scheme_id' => $model->id,
                        'service_id' => $serviceID,
                        'ranges' => isset($ranges[$serviceID]) ? $ranges[$serviceID] : [],
                        'legal' => isset($legal[$serviceID]) ? $legal[$serviceID] : [],
                        'group_id' => isset($group[$serviceID]) ? $group[$serviceID] : []
                    ]);

                    if(!$obSchemeServ->save())
                    {
                        $transaction->rollBack();
                        throw new ServerErrorHttpException();
                    }
                }
                $transaction->commit();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'arServices' => $arServices,
                'arLP' => $arLP,
                'arSchServ' => []
            ]);
        }
    }

    /**
     * Updates an existing PartnerSchemes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws InvalidParamException
     * @throws Exception
     * @throws ServerErrorHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $arServices = Services::getServicesMap();
        $arLP = \common\models\LegalPerson::getLegalPersonMap();
        $arSchServ = [];
        $arSchServOld = $model->partnerSchemesServices;
        foreach ($arSchServOld as $old)
            $arSchServ[$old->service_id] = $old;


        if ($model->load(Yii::$app->request->post()) && $model->validate()){
            $transaction = Yii::$app->db->beginTransaction();
            if($model->save())
            {
                $legal = Yii::$app->request->post('legal',[]);
                $ranges = Yii::$app->request->post('range',[]);
                $group = Yii::$app->request->post('group',[]);
                $model->unlinkAll('partnerSchemesServices',TRUE); //удаляем старые услуги
                if(!empty($ranges))
                    foreach ($ranges as &$rangeItem)
                        if(!empty($rangeItem))
                            foreach ($rangeItem as &$item)
                            {
                                if(isset($item['left'],$item['right'],$item['percent']))
                                {
                                    $item['left'] = CustomHelperMoney::convertNumberToValid($item['left']);
                                    $item['right'] = CustomHelperMoney::convertNumberToValid($item['right']);
                                    $item['percent'] = CustomHelperMoney::convertNumberToValid($item['percent']);
                                }
                            }
                $rows = [];
                foreach($arSchServOld as $item)
                {
                    $rows []= [
                        '',
                        $model->id,
                        $item->service_id,
                        Json::encode($item->ranges),
                        Json::encode($item->legal),
                        time(),
                        time(),
                        $item->group_id
                    ];
                }
                $historyModel = new PartnerSchemesServicesHistory();    //пишем историю
                if(!Yii::$app->db->createCommand()
                    ->batchInsert(PartnerSchemesServicesHistory::tableName(), $historyModel->attributes(), $rows)
                    ->execute())
                {
                    $transaction->rollBack();
                    throw new ServerErrorHttpException();
                }

                foreach ($arServices as $serviceID => $serviceName)
                {
                    $obSchemeServ = new PartnerSchemesServices([
                        'scheme_id' => $model->id,
                        'service_id' => $serviceID,
                        'ranges' => isset($ranges[$serviceID]) ? $ranges[$serviceID] : [],
                        'legal' => isset($legal[$serviceID]) ? $legal[$serviceID] : [],
                        'group_id' => isset($group[$serviceID]) ? $group[$serviceID] : []
                    ]);

                    if(!$obSchemeServ->save())
                    {
                        $transaction->rollBack();
                        throw new ServerErrorHttpException();
                    }
                }

                $transaction->commit();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
                'model' => $model,
                'arServices' => $arServices,
                'arLP' => $arLP,
                'arSchServ' => $arSchServ
        ]);
    }

    /**
     * Deletes an existing PartnerSchemes model.
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
     * Finds the PartnerSchemes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PartnerSchemes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PartnerSchemes::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
