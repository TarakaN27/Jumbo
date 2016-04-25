<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 14.08.15
 */

namespace common\components\customComponents\gridView;


use yii\grid\GridView;
use Closure;
use yii\helpers\Html;

class CustomGridView extends GridView{

    public
        $addTrClass = NULL,
        $addTrData = [];

    /**
     * Renders a table row with the given data model and key.
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key associated with the data model
     * @param integer $index the zero-based index of the data model among the model array returned by [[dataProvider]].
     * @return string the rendering result
     */
    public function renderTableRow($model, $key, $index)
    {
        $cells = [];
        /* @var $column Column */
        foreach ($this->columns as $column) {
            $cells[] = $column->renderDataCell($model, $key, $index);
        }
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;
        if(!empty($this->addTrData))
        {
            foreach($this->addTrData as $dt)
                $options['data-tr-'.$dt] = $model->$dt;
        }
        
        if(!empty($this->addTrClass) && $this->addTrClass instanceof Closure)
        {
            $tmpClass = call_user_func($this->addTrClass,$model);
            if(isset($options['class']))
                $options['class'].=' '.$tmpClass;
            else
                $options['class']=' '.$tmpClass;
        }

        return Html::tag('tr', implode('', $cells), $options);
    }

} 