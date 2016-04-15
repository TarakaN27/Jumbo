<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.4.16
 * Time: 17.14
 */

namespace backend\modules\partners\controllers;


use backend\components\AbstractBaseBackendController;
use common\models\CUser;
use common\models\PartnerCuserServ;

use common\models\search\CUserSearch;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\AbstractModel;
use Exception;
use yii\helpers\ArrayHelper;

class PartnersController extends AbstractBaseBackendController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CUserSearch();
        $dataProvider = $searchModel->searchPartners(Yii::$app->request->queryParams);

        return $this->render('index',[
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    /***
     * @param $pid
     * @return string
     */
    public function actionLinkLead($pid)
    {
        $query = PartnerCuserServ::find()->where(['partner_id' => $pid]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $this->render('link-lead',[
            'dataProvider' => $dataProvider,
            'pid' => $pid
        ]);
    }

    public function actionAddLink($pid)
    {
        $models =  [new PartnerCuserServ(['partner_id' => $pid])];
        $select2Data = [];

        if(Yii::$app->request->post('PartnerCuserServ'))
        {
            $models = AbstractModel::createMultiple(PartnerCuserServ::classname());
            foreach ($models as &$mod)
                $mod->partner_id = $pid;
            AbstractModel::loadMultiple($models,Yii::$app->request->post());
            $valid = AbstractModel::validateMultiple($models);
            if($valid)
            {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $bFlag = TRUE;
                    foreach ($models as $model)
                        if(!$model->save())
                        {
                            $bFlag = FALSE;
                            break;
                        }
                    if($bFlag)
                    {
                        $transaction->commit();
                        return $this->redirect(['link-lead','pid' => $pid]);
                    }else{
                        $transaction->rollBack();
                    }
                }catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
            $arCUserID = [];
            foreach ($models as $model)
                if(!empty($model->cuser_id) && !in_array($model->cuser_id,$arCUserID))
                    $arCUserID [] = $model->cuser_id;
            if($arCUserID)
                $select2Data = ArrayHelper::map(
                    CUser::find()->where(['id' => $arCUserID])->with('requisites')->all(),
                    'id',
                    'infoWithSite'
                );
        }
        return $this->render('add-link',[
            'models' => $models,
            'select2Data' => $select2Data,
            'pid' => $pid
        ]);
    }

}