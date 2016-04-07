<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 7.4.16
 * Time: 15.20
 */

namespace backend\controllers;

use backend\components\AbstractBaseBackendController;
use common\models\CrmLogs;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Response;
use yii\data\Pagination;

class AjaxHistoryController extends AbstractBaseBackendController
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
                    'roles' => ['@'],
                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'add-comment' => ['load-history'],
                    ],
                ],
            ]
        ];
        return $tmp;
    }
    /**
     * Контроллер по умолчанию всегда возвращает json!!!!
     */
    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::init();
    }

    /**
     *
     */
    public function actionLoadHistory()
    {
        $page = (int)\Yii::$app->request->post('page');
        $entity = \Yii::$app->request->post('entity');
        $itemID = (int)\Yii::$app->request->post('itemID');

        $query = CrmLogs::find()
            ->where([
                'entity' => $entity,
                'item_id' => $itemID
            ]);

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
        ]);
        $pages->setPageSize(10);
        $pages->setPage($page);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('updated_at DESC')
            
            ->all();

        $pageCount = $pages->getPageCount();
        $nextPage = NULL;
        if(($page+1) < $pageCount)
            $nextPage = $page+1;

        return [
            'tr' => $this->renderPartial('_li_history',['models' => $models]),
            'page' => $nextPage
        ];
    }
}