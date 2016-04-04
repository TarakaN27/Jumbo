<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\ExpenseCategories;
use common\models\LegalPerson;
use Yii;
use common\models\Expense;
use common\models\search\ExpenseSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * ExpenseController implements the CRUD actions for Expense model.
 */
class ExpenseController extends AbstractBaseBackendController
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
                    'actions' => ['index','create','view','update'],
                    'allow' => true,
                    'roles' => ['bookkeeper']
                ],
                [
                    'allow' => true,
                    'roles' => ['superadmin']
                ]
            ]
        ];
        return $tmp;
    }

    /**
     * Lists all Expense models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExpenseSearch();

        $addWhere = NULL;
        if(!Yii::$app->user->can('superRights'))
        {
            $cats = ExpenseCategories::getExpenseCatMap();
            if(empty($cats))
            {
                $addWhere = ' 1=0 ';
            }else{
                $addWhere = '('.LegalPerson::tableName().'.admin_expense is NULL OR '.LegalPerson::tableName().'.admin_expense = 0 )';
                $addWhere.= 'AND cat_id IN ('.implode(',',array_keys($cats)).')';
            }
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$addWhere);
        $arTotal = $searchModel->totalCount(Yii::$app->request->queryParams,$addWhere);


        if(empty($searchModel->pay_date))
            $searchModel->pay_date = NULL;

        // Get the initial city description
        $cuserDesc = empty($searchModel->cuser_id) ? '' : \common\models\CUser::findOne($searchModel->cuser_id)->getInfoWithSite();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cuserDesc' => $cuserDesc,
            'arTotal' => $arTotal
        ]);
    }

    /**
     * Displays a single Expense model.
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
     * Creates a new Expense model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Expense();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            // Get the initial city description
            $cuserDesc = empty($model->cuser_id) ? '' : \common\models\CUser::findOne($model->cuser_id)->getInfoWithSite();
            return $this->render('create', [
                'model' => $model,
                'cuserDesc' => $cuserDesc
            ]);
        }
    }

    /**
     * Updates an existing Expense model.
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
            // Get the initial city description
            $cuserDesc = empty($model->cuser_id) ? '' : \common\models\CUser::findOne($model->cuser_id)->getInfoWithSite();
            return $this->render('update', [
                'model' => $model,
                'cuserDesc' => $cuserDesc
            ]);
        }
    }

    /**
     * Deletes an existing Expense model.
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
     * Finds the Expense model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Expense the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $cat = ExpenseCategories::getExpenseCatMap();
        $query = Expense::find()->joinWith('legal')->where([Expense::tableName().'.id' => $id,'cat_id' => array_keys($cat)]);
        if(!Yii::$app->user->can('superRights'))
        {
            $addWhere = LegalPerson::tableName().'.admin_expense is NULL OR '.LegalPerson::tableName().'.admin_expense = 0 ';
            $query->andWhere($addWhere);
        }

        if (($model = $query->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
