<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.08.15
 */

namespace common\components\widgets\moneyMaskInputWidget;

use common\components\widgets\moneyMaskInputWidget\assets\MoneyMaskInputAssets;
use yii\base\InvalidConfigException;

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\InputWidget;

class MoneyMaskInputWidget extends InputWidget{

    public
        $formID;

    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }
        $this->registerPlugin();
    }

    /**
     * Registers MultiSelect Bootstrap plugin and the related events
     */
    protected function registerPlugin()
    {
        $view = $this->getView();
        MoneyMaskInputAssets::register($view);
        $id = $this->options['id'];

        $js = "
            jQuery('#$id').on('keyup load',function(){
                var
                    val = $(this).val().replace(/\s+/g,'');
                var
                    tmp = val.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
                $(this).val(tmp);
            });

            var
                    val = jQuery('#$id').val().replace(/\s+/g,'');
                var
                    tmp = val.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
                jQuery('#$id').val(tmp);

            $('#".$this->formID."').on('beforeValidate', function (event, messages, deferreds) {
                val = jQuery('#$id').val().replace(/\s+/g,'');
                jQuery('#$id').val(val);
                return true;
            });



            $('#".$this->formID."').on('afterValidate', function (event, messages, deferreds) {
                var
                    val = jQuery('#$id').val().replace(/\s+/g,'');
                var
                    tmp = val.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
                jQuery('#$id').val(tmp);
            });

            ";
        $view->registerJs($js,View::POS_READY);
    }
} 