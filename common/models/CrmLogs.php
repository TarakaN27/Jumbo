<?php

namespace common\models;

use backend\models\BUser;
use Yii;

/**
 * This is the model class for table "{{%crm_logs}}".
 *
 * @property integer $id
 * @property string $entity
 * @property integer $item_id
 * @property integer $changed_by
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 */
class CrmLogs extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_logs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity', 'changed_by'], 'required'],
            [['item_id', 'changed_by', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['entity'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/crm', 'ID'),
            'entity' => Yii::t('app/crm', 'Entity'),
            'item_id' => Yii::t('app/crm', 'Item ID'),
            'changed_by' => Yii::t('app/crm', 'Changed By'),
            'description' => Yii::t('app/crm', 'Description'),
            'created_at' => Yii::t('app/crm', 'Created At'),
            'updated_at' => Yii::t('app/crm', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuser()
    {
        return $this->hasOne(BUser::className(),['id' => 'changed_by']);
    }
}
