<?php

namespace backend\modules\messenger\controllers;

use backend\components\AbstractBaseBackendController;
use backend\models\BUser;
use common\models\Dialogs;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;

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
                    'allow' => true,
                    'roles' => ['admin','bookkeeper','moder']
                ]
            ]
        ];
        return $tmp;
    }

    public function actionIndex()
    {
        $userID = \Yii::$app->user->id;
        /*
        $query = Dialogs::find()->joinWith('busers')
            ->with([
                'busers' => function ($query) use ($userID)  {
                        $query->andWhere(BUser::tableName().'.id is NULL OR '.
                            BUser::tableName().'.id = '.$userID
                        );
                    }
            ])
            ->where([Dialogs::tableName().'.status' => Dialogs::PUBLISHED,'type' => Dialogs::TYPE_MSG])
            ->orWhere([Dialogs::tableName().'.buser_id' => $userID,'type' => Dialogs::TYPE_MSG]);
*/

        $query = Dialogs::find()
            ->joinWith('busers')
            ->where([
                Dialogs::tableName().'.status' => Dialogs::PUBLISHED,
                Dialogs::tableName().'.buser_id' => $userID,
                Dialogs::tableName().'.type' => Dialogs::TYPE_MSG
            ])
            ->orWhere(
                Dialogs::tableName().'.status = '.Dialogs::PUBLISHED.' AND '.Dialogs::tableName().'.type = :type'.
                ' AND ('.BUser::tableName().'.id is NULL OR '.BUser::tableName().'.id = '.$userID.' )',[
                ':type' => Dialogs::TYPE_MSG
            ])
            ;


        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(' DISTINCT '.Dialogs::tableName().'.id')
        ]);
        $pages->setPageSize(10);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->groupBy(Dialogs::tableName().'.id')
            ->orderBy(Dialogs::tableName().'.id DESC ')
            ->all();

        return $this->render('index', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }
}
