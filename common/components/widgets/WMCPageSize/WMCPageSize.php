<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 11.08.15
 */

namespace common\components\widgets\WMCPageSize;

use nterms\pagesize\PageSize;
use Yii;
use yii\helpers\Html;

class WMCPageSize extends PageSize{

    public function init()
    {
        parent::init();
        $this->options = [
            'class' => 'form-control input-sm',
        ];
        $this->label = \Yii::t('app/common','Show by');
        $this->template = '<div class="col-md-1 col-sm-1 col-xs-12 pull-right per-page-select ">{label}{list}</div>';
        $this->sizes = [
            5 => 5,
            10 => 10,
            15 => 15,
            20 => 20,
            25 => 25,
            50 => 50,
            100 => 100,
            200 => 200,
            500 => 500,
            1000 => 1000
        ];
        if(Yii::$app->session->get('per-page')){
            $this->defaultPageSize = Yii::$app->session->get('per-page');
        }else{
            $this->defaultPageSize = Yii::$app->params['defaultPageSize'];
        }

    }

    public function run(){
        if(empty($this->options['id'])) {
            $this->options['id'] = $this->id;
        }

        if($this->encodeLabel) {
            $this->label = Html::encode($this->label);
        }

        $perPage = !empty($_GET[$this->pageSizeParam]) ? $_GET[$this->pageSizeParam] : $this->defaultPageSize;
        Yii::$app->session->set('per-page',$perPage);

        $listHtml = Html::dropDownList('per-page', $perPage, $this->sizes, $this->options);
        $labelHtml = Html::label($this->label, $this->options['id'], $this->labelOptions);

        $output = str_replace(['{list}', '{label}'], [$listHtml, $labelHtml], $this->template);

        return $output;

    }
} 