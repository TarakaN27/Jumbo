<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $path
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Files extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'path'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'path'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/common', 'ID'),
            'name' => Yii::t('app/common', 'Name'),
            'path' => Yii::t('app/common', 'Path'),
            'status' => Yii::t('app/common', 'Status'),
            'created_at' => Yii::t('app/common', 'Created At'),
            'updated_at' => Yii::t('app/common', 'Updated At'),
        ];
    }
}
