<?php

namespace backend\modules\bonus\controllers;

use backend\models\BUser;
use backend\modules\bonus\form\ConnectBonusToUserForm;
use common\models\BonusSchemeService;
use common\models\BonusSchemeServiceHistory;
use common\models\LegalPerson;
use Yii;
use common\models\BonusScheme;
use common\models\search\BonusSchemeSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use common\models\Services;
use yii\web\ServerErrorHttpException;

/**
 * DefaultController implements the CRUD actions for BonusScheme model.
 */
class DefaultController extends Controller
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
        if(in_array($model->type,[$model::TYPE_SIMPLE_BONUS,$model::TYPE_COMPLEX_TYPE]))
            $arLegal = LegalPerson::getLegalPersonMap();

        $arBServices = $model->services;

        $arUsers = $model->users;

        return $this->render('view', [
            'model' => $model,
            'arServices' => $arServices,
            'arLegal' => $arLegal,
            'arBServices' => $arBServices,
            'arUsers' => $arUsers
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
        if ($model->load(Yii::$app->request->post())) {
            $tr = Yii::$app->db->beginTransaction();
            $arServices = Services::getAllServices();
            if($model->save())  //сохраняем схему
            {
                $cost = Yii::$app->request->post('costs',[]);
                $multiple = Yii::$app->request->post('multiple',[]);
                $legal = Yii::$app->request->post('legal',[]);
                $monthPersent = Yii::$app->request->post('months',[]);
                foreach($arServices as $obServ)     //сохраняем услуги по схеме
                {
                    $obBSev = new BonusSchemeService([
                        'scheme_id' => $model->id,
                        'service_id' => $obServ->id,
                        'month_percent' => isset($monthPersent[$obServ->id]) ? $monthPersent[$obServ->id] : [],
                        'legal_person' => isset($legal[$obServ->id]) ? $legal[$obServ->id] : [],
                        'cost' => isset($cost[$obServ->id]) ? $cost[$obServ->id] : NULL,
                        'unit_multiple' => isset($multiple[$obServ->id]) ? 1 : NULL
                    ]);

                    if(!$obBSev->save())
                    {
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
            'arBServices' => []
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
        foreach($arBServicesTmp as $value)
            $arBServices[$value->service_id] = $value;

        if ($model->load(Yii::$app->request->post())) {
            $tr = Yii::$app->db->beginTransaction();
            $arServices = Services::getAllServices();
            if($model->save())  //сохраняем схему
            {
                $cost = Yii::$app->request->post('costs',[]);
                $multiple = Yii::$app->request->post('multiple',[]);
                $legal = Yii::$app->request->post('legal',[]);
                $monthPersent = Yii::$app->request->post('months',[]);
                $model->unlinkAll('services',TRUE); //удаляем старые услуги

                $rows = [];
                foreach($arBServicesTmp as $item)
                {
                    $rows []= [
                        '',
                        $model->id,
                        $item->service_id,
                        Json::encode($item->month_percent),
                        $item->cost,
                        $item->unit_multiple,
                        time(),
                        time(),
                        Json::encode($item->legal_person)
                    ];
                }
                $historyModel = new BonusSchemeServiceHistory();    //пишем историю
                if(!Yii::$app->db->createCommand()
                    ->batchInsert(BonusSchemeServiceHistory::tableName(), $historyModel->attributes(), $rows)
                    ->execute())
                {
                    $tr->rollBack();
                    throw new ServerErrorHttpException();
                }

                foreach($arServices as $obServ)     //сохраняем услуги по схеме
                {
                    $obBSev = new BonusSchemeService([
                        'scheme_id' => $model->id,
                        'service_id' => $obServ->id,
                        'month_percent' => isset($monthPersent[$obServ->id]) ? $monthPersent[$obServ->id] : [],
                        'legal_person' => isset($legal[$obServ->id]) ? $legal[$obServ->id] : [],
                        'cost' => isset($cost[$obServ->id]) ? $cost[$obServ->id] : NULL,
                        'unit_multiple' => isset($multiple[$obServ->id]) ? 1 : NULL
                    ]);

                    if(!$obBSev->save())
                    {
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
            'arBServices' => $arBServices

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
        if($obForm->load(Yii::$app->request->post()))
        {
            if($obForm->makeRequest())
            {
                Yii::$app->session->setFlash('success',Yii::t('app/bonus','User successfully connected'));
                return $this->redirect('index');
            }
        }
        $data = [];
        if(!empty($obForm->users))
            $data = ArrayHelper::map(
                BUser::find()->select(['id','fname','lname','mname'])->where(['id' => $obForm->users])->all(),
                'id','fio');
        return $this->render('connect_user',[
            'model' => $model,
            'obForm' => $obForm,
            'data' => $data
        ]);
    }
}
