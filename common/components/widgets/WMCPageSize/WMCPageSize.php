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
        $this->defaultPageSize = Yii::$app->params['defaultPageSize'];
    }
} 