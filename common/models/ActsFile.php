<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%acts_file}}".
 *
 * @property integer $id
 * @property integer $act_id
 * @property string $name
 * @property string $path
 * @property integer $sent
 * @property integer $is_default
 * @property integer $created_at
 * @property integer $updated_at
 */
class ActsFile extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%acts_file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['act_id'], 'required'],
            [['act_id', 'sent', 'is_default', 'created_at', 'updated_at'], 'integer'],
            [['name', 'path'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/documents', 'ID'),
            'act_id' => Yii::t('app/documents', 'Act ID'),
            'name' => Yii::t('app/documents', 'Name'),
            'path' => Yii::t('app/documents', 'Path'),
            'sent' => Yii::t('app/documents', 'Sent'),
            'is_default' => Yii::t('app/documents', 'Is Default'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
        ];
    }
}
