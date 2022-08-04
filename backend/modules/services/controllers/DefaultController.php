<?php

namespace backend\modules\services\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\models\LegalPerson;
use common\models\ServiceDefaultContract;
use Yii;
use common\models\Services;
use common\models\search\ServicesSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;


/**
 * DefaultController implements the CRUD actions for Services model.
 */
class DefaultController extends AbstractBaseBackendController
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
                    'actions' => ['index','view'],
                    'allow' => false,
                    'roles' => ['teamlead', 'teamlead_sale', 'teamlead_acc']
                ],
                [
                    'actions' => ['index','view'],
                    'allow' => true,
                    'roles' => ['admin','moder','sale']
                ],
                [
                    'allow' => true,
                    'roles' => ['admin']
                ]
            ]
        ];
        return $tmp;
    }


    /**
     * Lists all Services models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ServicesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Services model.
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
     * Creates a new Services model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Services();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

           $sAssName = '';
            if(!empty($model->b_user_enroll))
                $sAssName = is_object($obUser = BUser::findOne($model->b_user_enroll)) ? $obUser->getFio() : '';

            return $this->render('create', [
                'model' => $model,
                'sAssName' => $sAssName
            ]);
        }
    }

    /**
     * Updates an existing Services model.
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
            $sAssName = '';
            if(!empty($model->b_user_enroll))
                $sAssName = is_object($obUser = BUser::findOne($model->b_user_enroll)) ? $obUser->getFio() : '';
            return $this->render('update', [
                'model' => $model,
                'sAssName' => $sAssName
            ]);
        }
    }

    /**
     * Deletes an existing Services model.
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
     * Finds the Services model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Services the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Services::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionDefaultContracts($id)
    {
        $models = ServiceDefaultContract::find()->where(['service_id' => $id])->all();
        $legalPerson = LegalPerson::getLegalPersonMap();

        $arDC = [];
        foreach($models as $mod)
            $arDC[$mod->lp_id] = $mod;

        $arNumber = Yii::$app->request->post('number');
        $arDate = Yii::$app->request->post('date');

        if(is_array($arNumber) && is_array($arDate))
        {
            $trans = Yii::$app->db->beginTransaction();
            $bError = false;
            ServiceDefaultContract::deleteAll(['service_id' => $id]);
            foreach($arNumber as $key=>$num)
            {
                /** @var ServiceDefaultContract $obSDC */
                $obSDC = new ServiceDefaultContract();
                $obSDC->service_id = $id;
                $obSDC->lp_id = $key;
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
                return $this->redirect(['index']);
            }else{

                Yii::$app->session->setFlash('error','Ошибка');
                $trans->rollBack();
            }
        }
        return $this->render('default_contracts',[
            'legalPerson' => $legalPerson,
            'arDC' => $arDC
        ]);

    }
}
