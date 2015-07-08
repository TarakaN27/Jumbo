<?php

namespace backend\modules\services\controllers;

use backend\components\AbstractBaseBackendController;
use common\components\ExchangeRates\ExchangeRatesCBRF;
use common\components\ExchangeRates\ExchangeRatesNBRB;
use Yii;
use common\models\ExchangeRates;
use common\models\search\ExchangeRatesSearch;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * ExchangeRatesController implements the CRUD actions for ExchangeRates model.
 */
class ExchangeRatesController extends AbstractBaseBackendController
{

    /**
     * Переопределим поведение
     * @return array
     */
    /*
    public function behaviors()
    {
        $arTmp = parent::behaviors();
        $arTmp['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'delete' => ['post'],
                'exchange-rates' => ['post']
            ],
        ];
        return $arTmp;
    }
*/

    /**
     * Lists all ExchangeRates models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExchangeRatesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExchangeRates model.
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
     * Creates a new ExchangeRates model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ExchangeRates();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ExchangeRates model.
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
     * Deletes an existing ExchangeRates model.
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
     * Finds the ExchangeRates model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExchangeRates the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExchangeRates::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     */
    public function actionUpdateRates($id)
    {
        $model = $this->findModel($id);
        $nbrb = new ExchangeRatesNBRB($model->nbrb);
        $nbrbRate = $nbrb->makeRequest();

        $crb = new ExchangeRatesCBRF($model->cbr);
        $crbRate = $crb->makeRequest();

        if((!empty($nbrbRate) || $model->code == 0) && (!empty($crbRate) || $model->code == 0))
        {
            $model->cbr_rate = $crbRate;
            $model->nbrb_rate= $nbrbRate;
            if($model->save())
            {
                Yii::$app->session->setFlash('success','Курсы валют успешно обновлены!.');
            }else{
                Yii::$app->session->setFlash('error','Не удалось сохранить курсы валют.');
            }
        }else{
            Yii::$app->session->setFlash('error','Не удалось получить курсы валют.');
        }

        return $this->redirect(['view','id' => $id]);
    }
}
