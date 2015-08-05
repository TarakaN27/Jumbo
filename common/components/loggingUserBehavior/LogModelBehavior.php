<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 04.08.15
 * Поведение для логирования действий пользователей с моделями.
 * Логирование полных действий(обновление, удаление, создание)
 * также логирование действий при изменении опрееленных полей
 */

namespace common\components\loggingUserBehavior;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;

class LogModelBehavior extends Behavior{

    private
        $_oldAttributes = [];

    public
        $active = true,
        $ignored = [],
        $allowed = [];

    /**
     * Назначаем событиям обработчики
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Получаем пользователя, выполняющего действие
     * @return int|string
     */
    protected function getUserID()
    {
        if(\Yii::$app->user->isGuest)
            return 'is_guest';
        else
            return \Yii::$app->user->id;
    }

    /**
     * после того как нашли сохраним старые значения модели
     */
    public function afterFind()
    {
        $this->setOldAttribute($this->owner->getAttributes());
    }

    public function setOldAttribute($value)
    {
        $this->_oldAttributes = $value;
    }

    public function getOldAttribute()
    {
        return $this->_oldAttributes;
    }

    public function leaveTrail($action, array $value = null, array $old_value = null)
    {
        if ($this->active) {
            $msg = 'AppID: '.\Yii::$app->id.
                '; UserID: '.$this->getUserID().
                '; Action:'.$action.
                '; Model class: '.$this->owner->className().
                '; ModelID: '.$this->getNormalizedPk().
                '; Value:'.Json::encode($value).
                '; OldValue:'.Json::encode($old_value);
            \Yii::info($msg,'pushUserBehaviors');
        }
        return true;
    }

    protected function getNormalizedPk()
    {
        $pk = $this->owner->getPrimaryKey();
        return is_array($pk) ? json_encode($pk) : $pk;
    }

    public function afterInsert()
    {
        $arAttr = $this->getValues();
        return $this->leaveTrail('INSERT',$arAttr);
    }

    public function afterDelete()
    {
        $arAttr = $this->getValues();
        return $this->leaveTrail('DELETE',$arAttr);
    }


    public function afterUpdate()
    {
        $arAttr = $this->getValues();
        $arOldAttr = $this->getOldAttribute();
        if(empty($arAttr))
            return true;
        $arCompare = $this->compareValue($arAttr,$arOldAttr);
        if(!empty($arCompare))
            $this->leaveTrail('UPDATE',$arCompare['value'],$arCompare['oldValue']);
        return TRUE;
    }


    protected function compareValue(array $arAttr,array $arOldAttr)
    {
        $arResult = [];
        foreach($arAttr as $key => $value)
        {
            if(isset($arOldAttr[$key]) && $arOldAttr[$key] != $value)
            {
                $arResult['value'][$key]= $value;
                $arResult['oldValue'][$key] = $arOldAttr[$key];
            }
        }
        return $arResult;
    }

    protected function getValues()
    {
        $attributes = $this->owner->getAttributes();
        if(empty($this->allowed) && empty($this->ignored))
            return $attributes;
        else
        {
            foreach($attributes as $key=>$value)
            {
                if(
                    (!in_array($key,$this->allowed) && !empty($this->allowed)) ||
                    (in_array($key,$this->ignored) && !empty($this->ignored))
                )
                    unset($attributes[$key]);
            }
            return $attributes;
        }
    }


} 