<?php

namespace backend\modules\bookkeeping\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\CUser;
use common\models\CUserRequisites;
use common\models\ExchangeRates;
use common\models\Expense1CCategories;
use common\models\Expense1CLink;
use common\models\ExpenseCategories;
use common\models\LegalPerson;
use Yii;
use common\models\Expense;
use common\models\search\ExpenseSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use backend\modules\bookkeeping\form\Migrate1CLoadFileForm;
use common\models\AbstractModel;

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
                    'actions' => ['index','create','view','update','migrate-1c'],
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

    public function actionMigrate1c()
    {
        $model = new Migrate1CLoadFileForm();
        if (Yii::$app->request->isPost) {
            if(isset($_FILES['Migrate1CLoadFileForm']) && $_FILES['Migrate1CLoadFileForm']['tmp_name']){
                  $models = $this->parseXml($_FILES['Migrate1CLoadFileForm']['tmp_name']['src']);
                  if($models) {
                      return $this->render('migrate_1c_form_list', [
                          'models' => $models,
                      ]);
                  }else {
                      Yii::$app->session->setFlash('danger', Yii::t('app/book', 'Dont have expense for loading'));
                      return $this->redirect(['index']);
                  }
            }else{
                $savedModels = [];
                $notSavedmodels = [];
                foreach(Yii::$app->request->post('Expense') as $item){
                    $model = new Expense($item);
                    $model->legal_id =3;
                    if($model->active) {
                        if ($model->validate()) {
                            $model->save(false);
                            $savedModels[] = $model;
                        } else {
                            $notSavedmodels[] = $model;
                        }
                    }
                }
                if($savedModels){
                    Yii::$app->session->setFlash('success', Yii::t('app/book', '{count} expenses success saved', ['count'=> count($savedModels)]));
                    foreach($savedModels as $item){
                            $sqlPart[] = "($item->cuser_id, $item->category1CId, $item->cat_id, 1)";
                    }
                    $sql = "INSERT INTO wm_expense_1c_link (cuser_id, category_1c_id, jumbo_category_id, `count`) VALUES " . implode(',', $sqlPart) . "ON DUPLICATE KEY UPDATE wm_expense_1c_link.count = wm_expense_1c_link.count + VALUES(count)";
                    $db = Yii::$app->db;
                    $db->createCommand($sql)->execute();
                }
                if($notSavedmodels){
                    return $this->render('migrate_1c_form_list', [
                        'models' => $notSavedmodels,
                    ]);
                }else{
                    return $this->redirect(['index']);
                }
            }
        }
        return $this->render('migrate_1c', [
            'model' => $model,
        ]);
    }
    protected function parseXml($xml){
        $expense1CIds = [];
        $expenses = simplexml_load_file($xml);
        $unp = [];
        $currenciesCode = [];
        foreach($expenses as $expense){
            $date = strtotime($expense->{"ДатаДокумента"});
            $id1c = date("Y-m-d",$date).'-'.$expense->{"НомерДокумента"};
            $expense1CIds[] = $id1c;
            if(trim($expense->{"УНП"}) != ""){
                $unp[] = trim($expense->{"УНП"});
            }
            $currenciesCode[] = $expense->{"Валюта"};
            if(trim($expense->{"ДДС"})!=""){
                $categories1CNames[] = trim($expense->{"ДДС"});
            }
        }
        $extendsExpense =  Expense::find()->where(['id_1c'=>$expense1CIds])->indexBy('id_1c')->all();

        $cusers = CUserRequisites::find()->where(['ynp'=>array_unique($unp)])->indexBy('ynp')->all();
        foreach($cusers as $item){
            $cuserIds[] =  $item->id;
        }
        $categories1C = Expense1CCategories::find()->where(['name'=>array_unique($categories1CNames)])->indexBy('id')->all();
        $existName = ArrayHelper::getColumn($categories1C,'name');
        $notExist1CCategory = array_diff(array_unique($categories1CNames),$existName);
        foreach($notExist1CCategory as $item){
            $category1C = new Expense1CCategories();
            $category1C->name = $item;
            $category1C->save();
            $categories1C[$category1C->id] = $category1C;
        }
        $arCategories1C = ArrayHelper::getColumn($categories1C,'name');

        $link = [];
        if($categories1C && $cusers){
            $link = Expense1CLink::find()
                ->select(['cuser_id','category_1c_id', 'jumbo_category_id'])
                ->where(['cuser_id'=>array_unique(ArrayHelper::getColumn($cusers, 'id')), 'category_1c_id'=>array_keys($categories1C)])
                ->asArray()
                ->orderBy(['count'=>SORT_ASC])
                ->indexBy(function($model){
                    return $model['cuser_id'].'-'.$model['category_1c_id'];
                })
                ->all();
        }
        $currencies = ExchangeRates::find()->where(['code'=>$currenciesCode])->indexBy('code')->all();
        $models = [];
        foreach($expenses as $expense){
            $date = strtotime($expense->{"ДатаДокумента"});
            $id1c = date("Y-m-d",$date).'-'.$expense->{"НомерДокумента"};
            if(!isset($extendsExpense[$id1c])) {
                $model = new Expense();
                $model->id_1c = $id1c;
                $model->pay_date = strtotime($expense->{"ДатаДокумента"});
                $model->pay_summ = $expense->{"Сумма"};
                $model->description = $expense->{"НазначениеПлатежа"};
                $model->cuser_id = isset($cusers[trim($expense->{"УНП"})]->id)?$cusers[trim($expense->{"УНП"})]->id:null;
                if($model->cuser_id){
                    $model->cuserName = $cusers[trim($expense->{"УНП"})]->getCorpName();
                }
                $key=  $expense->{"Валюта"}.'';
                $model->currency_id = isset($currencies[$key])?$currencies[$key]->id:null;
                if($key = array_search(trim($expense->{"ДДС"}), $arCategories1C)) {
                    $model->category1CId = $key;
                    if (isset($link[$model->cuser_id.'-'.$key])){
                        $model->cat_id = $link[$model->cuser_id.'-'.$key]['jumbo_category_id'];
                    }
                }
                if(!$model->cat_id && $model->cuser_id){
                    $cat = Expense::find()->select(['countCat'=>'COUNT(cat_id)', 'cat_id'])->where(['cuser_id'=>$model->cuser_id])->groupBy('cat_id')->orderBy(['countCat'=>SORT_DESC])->asArray()->one();
                    if($cat){
                        $model->cat_id = $cat['cat_id'];
                    }
                }
                $models[] = $model;
            }
        }
        return $models;
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
        if ($model->load(Yii::$app->request->post())) {
            if($model->save())
            {
                return $this->redirect(['view', 'id' => $model->id]);
            }
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
