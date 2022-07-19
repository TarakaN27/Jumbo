<?php

namespace backend\modules\users\controllers;

use backend\components\AbstractBaseBackendController;
use backend\modules\users\form\BindMembersForm;
use backend\modules\users\models\ChangePasswordBUserForm;
use common\models\BUserCrmRules;
use common\models\BuserInviteCode;
use Yii;
use backend\models\BUser;
use backend\models\search\BUserSearch;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
/**
 * DefaultController implements the CRUD actions for BUser model.
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
                    'actions' => ['profile', 'edit-profile','change-own-password'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
                [
                    'actions' => ['bind-members'],
                    'allow' => true,
                    'roles' => ['superadmin']
                ],
                [
                    'actions' => ['index','view'],
                    'allow' => true,
                    'roles' => ['moder','sale','bookkeeper']
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
     * Lists all BUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BUser model.
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
     * Creates a new BUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BUser();
        $model->setScenario(BUser::SCENARIO_REGISTER);
        $model->password = '123456'; //@todo после добавления инвайтов УДАЛИТЬ!!!!!

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing BUser model.
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
     * Deletes an existing BUser model.
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
     * Finds the BUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionChangePassword($id)
    {
        $model = new ChangePasswordBUserForm(['userID' => $id]);

        if($model->load(Yii::$app->request->post()) && $model->makeRequest())
        {
            Yii::$app->session->setFlash('success',Yii::t('app/users','Password_successfully_changed'));
            return $this->redirect(['view','id'=>$id]);
        }
        return $this->render('change_password',[
            'model' => $model,
            'id' => $id
        ]);
    }

    /**
     * @return string
     */
    public function actionProfile()
    {
        return $this->render('profile',[
            'model' => $this->findModel(Yii::$app->user->id)
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionEditProfile()
    {
        $model = $this->findModel(Yii::$app->user->id);

        if($model->load(Yii::$app->request->post()) && $model->save())
        {
            Yii::$app->session->setFlash('success',Yii::t('app/users','Profile successfully changed'));
            return $this->redirect(['profile']);
        }
        return $this->render('edit_profile',[
            'model' => $model
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionChangeOwnPassword()
    {
        $model = new ChangePasswordBUserForm(['userID' => Yii::$app->user->id]);

        if($model->load(Yii::$app->request->post()) && $model->makeRequest())
        {
            Yii::$app->session->setFlash('success',Yii::t('app/users','Password_successfully_changed'));
            return $this->redirect(['profile']);
        }
        return $this->render('change_password',[
            'model' => $model,
            'isProfile' => true
        ]);
    }

    public function actionBindMembers($id)
    {
        $model = new BindMembersForm(['userID' => $id]);

        if($model->load(Yii::$app->request->post()) && $model->makeRequest())
        {
            Yii::$app->session->setFlash('success',Yii::t('app/users','Members successfully binded'));
            return $this->redirect(['view','id' => $id]);
        }

        return $this->render('bind_members',[
            'model' => $model
        ]);
    }

}
