<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\models;

use yii\behaviors\TimestampBehavior;
use Yii;
use yii\helpers\ArrayHelper;

abstract class AbstractActiveRecord extends AbstractActiveRecordWTB{

    /**
     * вернем форматированную дату
     * @return bool|string
     */
    public function getFormatedCreatedAt()
    {
        return date('d.m.Y H:i',$this->created_at);
    }

    /**
     * вернем форматированную дату обновления записи
     * @return bool|string
     */
    public function getFormatedUpdatedAt()
    {
        return date('d.m.Y H:i',$this->updated_at);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $arTmp = parent::behaviors();
        return ArrayHelper::merge($arTmp,[
            TimestampBehavior::className(),
        ]);
    }
} 