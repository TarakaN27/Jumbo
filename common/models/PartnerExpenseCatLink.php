<?php
namespace common\models;

use Yii;
/**
 * This is the model class for table "{{%partner_expense_cat_link}}".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $legal_person_id
 * @property integer $service_id
 * @property integer $expanse_cat_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ExpenseCategories $expanseCat
 * @property LegalPerson $legalPerson
 * @property Services $service
 */
class PartnerExpenseCatLink extends AbstractActiveRecord
{
    CONST
        TYPE_MONEY = '5',
        TYPE_SERVICES = '10';

    /**
     * @return array
     */
    public static function getTypeMap()
    {
        return [
            self::TYPE_MONEY => Yii::t('app/users','Type money'),
            self::TYPE_SERVICES => Yii::t('app/users','Type services')
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_expense_cat_link}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'legal_person_id','expanse_cat_id'],'required'],
            [['type', 'legal_person_id', 'service_id', 'expanse_cat_id', 'created_at', 'updated_at'], 'integer'],
            [['expanse_cat_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseCategories::className(), 'targetAttribute' => ['expanse_cat_id' => 'id']],
            [['legal_person_id'], 'exist', 'skipOnError' => true, 'targetClass' => LegalPerson::className(), 'targetAttribute' => ['legal_person_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Services::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['type','legal_person_id','service_id'],'customValidateUnique'],
            //['service_id','required','when' => function($model){
            //    return $model->type == self::TYPE_MONEY;
            //}]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function customValidateUnique($attribute,$params)
    {
        if(self::find()->where([
            'type' => $this->type,
            'service_id' => $this->service_id,
            'legal_person_id' => $this->legal_person_id
        ])->exists())
            $this->addError($attribute,Yii::t('app/users','This link already exists'));
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'type' => Yii::t('app/users', 'Type'),
            'legal_person_id' => Yii::t('app/users', 'Legal Person ID'),
            'service_id' => Yii::t('app/users', 'Service ID'),
            'expanse_cat_id' => Yii::t('app/users', 'Expanse Cat ID'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }

    /**
     * @return mixed|null
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeMap();
        return isset($tmp[$this->type]) ? $tmp[$this->type] : NULL;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpanseCat()
    {
        return $this->hasOne(ExpenseCategories::className(), ['id' => 'expanse_cat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLegalPerson()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'legal_person_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * Get expense category by service id, legal person id, type
     * @param $serviID
     * @param $legalPersonID
     * @param $iType
     * @return mixed
     */
    public static function getCatByServAndLP($serviID,$legalPersonID,$iType)
    {
        return self::find()->where(['service_id' => $serviID,'legal_person_id' => $legalPersonID,'type' => $iType])->one();
    }

}
