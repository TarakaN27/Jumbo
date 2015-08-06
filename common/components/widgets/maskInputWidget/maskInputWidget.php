<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.08.15
 */

namespace common\components\widgets\maskInputWidget;


use common\components\widgets\maskInputWidget\assets\MaskInputAssets;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Html;

class maskInputWidget extends Widget{

    public
        $mask,
        $clientOptions = [

    ];

    public function init()
    {
        parent::init();
        $this->validate();
    }

    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }
        $this->registerPlugin();
    }

    protected function validate()
    {
        if(empty($this->mask) || !is_string($this->mask))
            throw new InvalidConfigException('You must set mask correctly');
    }

    /**
     * Registers MultiSelect Bootstrap plugin and the related events
     */
    protected function registerPlugin()
    {
        $view = $this->getView();
        MaskInputAssets::register($view);
        $id = $this->options['id'];
        $options = $this->clientOptions !== false && !empty($this->clientOptions)
            ? Json::encode($this->clientOptions)
            : '{}';
        $js = "jQuery('#$id').mask('".$this->mask."',$options);";
        $view->registerJs($js);
    }
} 