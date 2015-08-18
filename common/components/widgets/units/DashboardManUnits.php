<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 18.08.15
 */

namespace common\components\widgets\units;


use app\models\UnitsToManager;
use backend\models\BUser;
use yii\base\Widget;

class DashboardManUnits extends Widget{


    public function init()
    {

    }

    public function run()
    {
        if(\Yii::$app->user->isGuest || \Yii::$app->user->identity->role != BUser::ROLE_MANAGER)
            return NULL;

        $arData = UnitsToManager::getManagerUnitsByCurrMonthRange(\Yii::$app->user->id);
        return $this->render('dashboard_man_units',[
            'iTotalCost' => $arData['iTotalCost'],
            'iTotalUnits' => $arData['iTotalUnits'],
        ]);
    }

} 