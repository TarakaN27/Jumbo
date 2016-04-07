<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 7.4.16
 * Time: 14.32
 */

namespace common\components\behavior\history;

use common\models\CrmLogs;
use yii\base\Behavior;
use yii\helpers\BaseStringHelper;

class ModelHistoryBehavior extends Behavior
{
    public
        $changedFields,
        $arChangedFieldsDescription;

    /**
     * @return array
     */
    public function events()
    {
        return [
            CrmLogs::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            CrmLogs::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    /**
     * @return bool
     */
    public function beforeUpdate()
    {
        $oldAttributes = $this->owner->getOldAttributes();
        $oldModel = new $this->owner($oldAttributes);
        foreach($this->changedFields as $field => $method)
        {
            if($this->owner->isAttributeChanged($field) )
            {
                $oldValue = $method ? $oldModel->$method() : $oldModel->$field;
                $newValue = $method ? $this->owner->$method() : $this->owner->$field;
                if($oldValue != $newValue)
                    $this->arChangedFieldsDescription []= \Yii::t('app/msg','Field {field} from {oldValue} to {newValue}',[
                        'field' => $this->owner->getAttributelabel($field),
                        'oldValue' => $oldValue,
                        'newValue' => $newValue
                    ]);
            }
        }
        unset($oldModel);

        return TRUE;
    }

    /**
     * @return bool
     */
    public function afterUpdate()
    {
        $obDialog = $this->owner->dialog;
        // изменение полей. добавляем комментарий
        if(!empty($this->arChangedFieldsDescription) && is_object($obDialog)) {
            $msg = \Yii::t('app/msg', 'User {user} make changes:', [
                    'user' => \Yii::$app->user->identity->getFio(),
                ]) . ' </br>' . implode(',</br>', $this->arChangedFieldsDescription);
            
            $obCrmLog = new CrmLogs();
            $obCrmLog->changed_by = \Yii::$app->user->id;
            $obCrmLog->entity = BaseStringHelper::basename($this->owner->className());
            $obCrmLog->item_id = $this->owner->id;
            $obCrmLog->description = $msg;
            $obCrmLog->save();
        }
        return TRUE;
    }

}