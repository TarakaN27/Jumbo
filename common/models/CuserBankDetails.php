<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%legal_person}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $legal_person_id
 * @property integer $status
 * @property string $bank_details
 * @property integer $updated_at
 * @property integer $created_at
 */
class CuserBankDetails extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_link_bank_details}}';
    }
    public function behaviors()
    {
        $arTmp = parent::behaviors();
        foreach($arTmp as $key=>$temp){
            if(!is_array($temp) && strpos($temp,"Timestamp")){
                unset($arTmp[$key]);
            }
        }
        return $arTmp;
    }
}
