<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 06.07.15
 */

namespace common\models;


use yii\db\ActiveRecord;
use Yii;

abstract class AbstractActiveRecordWTB extends ActiveRecord{

    CONST
        PUBLISHED = 1,
        UNPUBLISHED = 0;

    /**
     * Статусы записей
     * @return array
     */
    public static function getStatusArr()
    {
        return [
            self::PUBLISHED => Yii::t('app/common','Published'),
            self::UNPUBLISHED => Yii::t('app/common','Unpublished')
        ];
    }

    /**
     * @return string
     */
    public function getStatusStr()
    {
        $arSts = self::getStatusArr();
        return isset($arSts[$this->status]) ? $arSts[$this->status] : 'N/A';
    }
} 