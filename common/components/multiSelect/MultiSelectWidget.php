<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 29.07.15
 * http://loudev.com/#home
 */

namespace common\components\multiSelect;

use common\components\multiSelect\assets\MultiSelectAssets;
use yii\base\InvalidConfigException;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;

class MultiSelectWidget extends InputWidget{

    public $data = [];
    public $clientOptions = [

    ];

    public function init()
    {
        $this->validate();
        parent::init();

    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if(!array_key_exists('multiple',$this->options)) //так как плагин для мультиселекта, то добавляем
            $this->options['multiple'] = 'multiple';
        echo Html::a(\Yii::t('app/common','Select all'),'#',['id'=>'select-all-'.$this->options['id']]);
        echo ' / ';
        echo Html::a(\Yii::t('app/common','Deselect all'),'#',['id' => 'deselect-all-'.$this->options['id']]);
        if ($this->hasModel()) {
            echo Html::activeDropDownList($this->model, $this->attribute, $this->data, $this->options);
        } else {
            echo Html::dropDownList($this->name, $this->value, $this->data, $this->options);
        }
        $this->registerPlugin();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function validate()
    {
        //if (empty($this->data)) {
        //    throw new  InvalidConfigException('"MultiSelect::$data" attribute cannot be blank or an empty array.');
       // }
    }

    /**
     * Registers MultiSelect Bootstrap plugin and the related events
     */
    protected function registerPlugin()
    {
        $view = $this->getView();
        MultiSelectAssets::register($view);
        $id = $this->options['id'];
        $options = $this->clientOptions !== false && !empty($this->clientOptions)
            ? Json::encode($this->clientOptions)
            : '';
        $js = "jQuery('#$id').multiSelect($options);";
        $js.= "$('#select-all-".$id."').click(function(){
                $('#$id').multiSelect('select_all');
              return false;
            });";
        $js.= "$('#deselect-all-".$id."').click(function(){
                $('#$id').multiSelect('deselect_all');
              return false;
            });";

        $view->registerJs($js);
    }

} 