<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 7.7.16
 * Time: 14.22
 */

namespace common\components\customComponents\validation;

use common\components\helpers\CustomHelperMoney;
use yii\validators\Validator;
use yii\web\View;

class ValidNumber extends Validator
{
    /**
     * @var boolean whether the filter should be skipped if an array input is given.
     * If true and an array input is given, the filter will not be applied.
     */
    public $skipOnArray = false;
    /**
     * @var boolean this property is overwritten to be false so that this validator will
     * be applied when the value being validated is empty.
     */
    public $skipOnEmpty = false;

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!$this->skipOnArray || !is_array($value)) {
            $model->$attribute = CustomHelperMoney::convertNumberToValid($value);
        }
    }
}