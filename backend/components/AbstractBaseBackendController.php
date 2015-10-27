<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace backend\components;


use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

abstract class AbstractBaseBackendController extends Controller{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    protected function convertErrorToStr($model)
    {
        if(!is_object($model) || empty($arErrors = $model->getErrors()))
            return '';

        $str = '';
        foreach($arErrors as $key=>$val)
        {
            $str.= $key.' - '.$val.'<br>';
        }

        return $str;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if(parent::beforeAction($action))
        {



            return TRUE;
        }
        return FALSE;
    }
} 