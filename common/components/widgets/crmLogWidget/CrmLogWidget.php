<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 7.4.16
 * Time: 15.24
 */

namespace common\components\widgets\crmLogWidget;


use common\components\widgets\crmLogWidget\assets\CrmLogAssets;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\Url;
use yii\web\View;

class CrmLogWidget extends Widget
{
    public
        $autoInit = false,
        $clickEventsItem = NULL,
        $entityName,
        $itemID;

    /**
     * @return mixed|string
     */
    public function run()
    {
        $this->validateParams();
        $this->registerAssets();
        return $this->render('crm-log',[
            'entityName' => $this->entityName,
            'itemID' => $this->itemID,
            'autoInit' => $this->autoInit,
            'clickEventsItem' => $this->clickEventsItem,
            'url' => Url::to(['/ajax-history/load-history'])
        ]);
    }

    /**
     *
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        CrmLogAssets::register($view);
        $scriptInit = '';
        if($this->autoInit)
            $scriptInit = "$(function(){initEntityHistory();});";
        elseif(!empty($this->clickEventsItem))
            $scriptInit = "$(function(){ $('".$this->clickEventsItem."').on('click',initEntityHistory);});";

        $view->registerJs($scriptInit,View::POS_END);
    }

    /**
     *
     */
    protected function validateParams()
    {
        if(empty($this->entityName))
            throw new InvalidParamException('Entity name must be set');

        if(empty($this->itemID))
            throw  new InvalidParamException('Item ID must be set');
    }


}