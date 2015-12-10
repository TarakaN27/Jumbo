<?php

namespace backend\modules\config\controllers;

use common\models\AbstractModel;
use common\models\BUserCrmRules;
use Yii;
use common\models\BUserCrmRoles;
use common\models\search\BUserCrmRolesSearch;
use backend\components\AbstractBaseBackendController;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * BUserCrmRolesController implements the CRUD actions for BUserCrmRoles model.
 */
class BUserCrmRolesController extends AbstractBaseBackendController
{
    /**
     * Lists all BUserCrmRoles models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BUserCrmRolesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BUserCrmRoles model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $arRules = $model->bUserCrmRules;
        return $this->render('view', [
            'model' => $model,
            'arRules' => $arRules
        ]);
    }

    /**
     * Creates a new BUserCrmRoles model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $modelRole = new BUserCrmRoles();
        $modelRule = [new BUserCrmRules()];
        if ($modelRole->load(Yii::$app->request->post())) {

            $modelRule = AbstractModel::createMultiple(BUserCrmRules::classname());
            AbstractModel::loadMultiple($modelRule, Yii::$app->request->post());

            // ajax validation
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::merge(
                    ActiveForm::validateMultiple($modelRule),
                    ActiveForm::validate($modelRole)
                );
            }

            // validate all models
            $valid = $modelRole->validate();
            $valid = AbstractModel::validateMultiple($modelRule) && $valid;

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $modelRole->save(false)) {
                        foreach ($modelRule as $modelAddress) {
                            $modelAddress->role_id = $modelRole->id;
                            if (! ($flag = $modelAddress->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }
                    if ($flag) {
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $modelRole->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('create', [
            'model' => $modelRole,
            'modelRule' => (empty($modelRule)) ? [new BUserCrmRules()] : $modelRule
        ]);
    }

    /**
     * Updates an existing BUserCrmRoles model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $modelRole = $this->findModel($id);
        $modelsRule = $modelRole->bUserCrmRules;

        if ($modelRole->load(Yii::$app->request->post())) {

            $oldIDs = ArrayHelper::map($modelsRule, 'id', 'id');
            $modelsRule = AbstractModel::createMultiple(BUserCrmRules::classname(), $modelsRule);
            AbstractModel::loadMultiple($modelsRule, Yii::$app->request->post());
            $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelsRule, 'id', 'id')));

            // ajax validation
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::merge(
                    ActiveForm::validateMultiple($modelsRule),
                    ActiveForm::validate($modelRole)
                );
            }

            // validate all models
            $valid = $modelRole->validate();
            $valid = AbstractModel::validateMultiple($modelsRule) && $valid;

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $modelRole->save(false)) {
                        if (! empty($deletedIDs)) {
                            BUserCrmRules::deleteAll(['id' => $deletedIDs]);
                        }
                        foreach ($modelsRule as $modelAddress) {
                            $modelAddress->role_id = $modelRole->id;
                            if (! ($flag = $modelAddress->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }
                    if ($flag) {
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $modelRole->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('update', [
            'model' => $modelRole,
            'modelRule' => (empty($modelsRule)) ? [new BUserCrmRules] : $modelsRule
        ]);




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
     * Deletes an existing BUserCrmRoles model.
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
     * Finds the BUserCrmRoles model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BUserCrmRoles the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BUserCrmRoles::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
