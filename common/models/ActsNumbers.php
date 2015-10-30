<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%acts_numbers}}".
 *
 * @property integer $id
 * @property integer $acts_number
 * @property integer $allow
 */
class ActsNumbers extends AbstractActiveRecordWTB
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%acts_numbers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['acts_number', 'allow'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/documents', 'ID'),
            'acts_number' => Yii::t('app/documents', 'Acts Number'),
            'allow' => Yii::t('app/documents', 'Allow'),
        ];
    }
}
