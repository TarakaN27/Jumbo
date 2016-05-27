<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 27.5.16
 * Time: 14.53
 */

namespace common\components\behavior\acts;


use common\models\Acts;
use common\models\ActToPayments;
use common\models\Payments;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class ActsActionBehavior extends Behavior
{
    protected
        $arPaymentsIds = [];

    public function events()
    {
        return [
            Acts::EVENT_BEFORE_DELETE => 'beforeDelete',
            Acts::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }

    /**
     * Перед удалением сохраним платежи, для которых необходимо удалить активацию
     */
    public function beforeDelete()
    {
        /** @var Acts $model */
        $model = $this->owner;
        $this->arPaymentsIds = ArrayHelper::getColumn(ActToPayments::find()->where(['act_id' => $model->id])->all(),'payment_id');
    }

    /**
     * После удаления откатим платежи(установим что они не актированы)
     */
    public function afterDelete()
    {
        if($this->arPaymentsIds)
            Payments::updateAll(['act_close' => Payments::NO],['id' => $this->arPaymentsIds]);
    }
}