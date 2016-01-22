<?php

namespace common\models;

use Yii;
use backend\models\BUser;
use yii\db\Query;

/**
 * This is the model class for table "{{%bills}}".
 *
 * @property integer $id
 * @property integer $manager_id
 * @property integer $cuser_id
 * @property integer $l_person_id
 * @property integer $service_id
 * @property integer $docx_tmpl_id
 * @property integer $amount
 * @property integer $bill_number
 * @property string $bill_date
 * @property integer $bill_template
 * @property integer $use_vat
 * @property string $vat_rate
 * @property string $description
 * @property string $object_text
 * @property string $buy_target
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $external
 * @property string $bsk
 *
 * @property Services $service
 * @property BillDocxTemplate $docxTmpl
 * @property LegalPerson $lPerson
 * @property BUser $manager
 */
class Bills extends AbstractActiveRecord
{

    CONST
        TYPE_DOC_DOCX = 1,
        TYPE_DOC_PDF = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bills}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                 'manager_id', 'cuser_id', 'l_person_id',
                 'service_id', 'docx_tmpl_id', 'amount',
                 'object_text', 'buy_target','offer_contract'
             ], 'required'],
            [[
                 'manager_id', 'cuser_id', 'l_person_id',
                 'service_id', 'docx_tmpl_id', 'amount',
                 'bill_number', 'bill_template', 'use_vat',
                 'created_at', 'updated_at','external'
             ], 'integer'],
            ['bsk','unique'],
            [['bill_date'], 'safe'],
            [['vat_rate'], 'number'],
            [['description', 'object_text','bsk'], 'string'],
            [['buy_target','offer_contract'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/documents', 'ID'),
            'manager_id' => Yii::t('app/documents', 'Manager ID'),
            'cuser_id' => Yii::t('app/documents', 'Cuser ID'),
            'l_person_id' => Yii::t('app/documents', 'L Person ID'),
            'service_id' => Yii::t('app/documents', 'Service ID'),
            'docx_tmpl_id' => Yii::t('app/documents', 'Docx Tmpl ID'),
            'amount' => Yii::t('app/documents', 'Amount'),
            'bill_number' => Yii::t('app/documents', 'Bill Number'),
            'bill_date' => Yii::t('app/documents', 'Bill Date'),
            'bill_template' => Yii::t('app/documents', 'Bill Template'),
            'use_vat' => Yii::t('app/documents', 'Use Vat'),
            'vat_rate' => Yii::t('app/documents', 'Vat Rate'),
            'description' => Yii::t('app/documents', 'Description'),
            'object_text' => Yii::t('app/documents', 'Object Text'),
            'buy_target' => Yii::t('app/documents', 'Buy Target'),
            'created_at' => Yii::t('app/documents', 'Created At'),
            'updated_at' => Yii::t('app/documents', 'Updated At'),
            'external' => Yii::t('app/documents', 'External'),
            'bsk' => Yii::t('app/documents', 'Bill secret key'),
            'offer_contract' => Yii::t('app/documents','offer_contract')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Services::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocxTmpl()
    {
        return $this->hasOne(BillDocxTemplate::className(), ['id' => 'docx_tmpl_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLPerson()
    {
        return $this->hasOne(LegalPerson::className(), ['id' => 'l_person_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(BUser::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCuser()
    {
        return $this->hasOne(CUser::className(), ['id' => 'cuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBTemplate()
    {
        return $this->hasOne(BillTemplate::className(), ['id' => 'bill_template']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->getBSK();
        if(parent::beforeSave($insert))
        {
            $this->setBillNumberAndDate();
            return TRUE;
        }
        return FALSE;
    }

    /**
     *
     */
    protected function setBillNumberAndDate()
    {
        $this->bill_date = date('Y-m-d');

        $iBNmr = (new Query())
            ->select('MAX(bill_number) as s')
            ->from(self::tableName())
            ->where(['l_person_id' => $this->l_person_id])
            ->scalar();

        if(empty($iBNmr) || !is_numeric($iBNmr))
            $iBNmr = 1;
        else
            $iBNmr++;

        $this->bill_number = $iBNmr;
    }

    /**
     * @return string
     */
    public function getBSK()
    {
        $tmp = Yii::$app->security->generateRandomString();
        if(self::find()->where(['bsk' => $tmp])->exists())
            return $this->getBSK();
        else
            return $this->bsk = $tmp;
    }

    /**
     *
     */
    public function updateForCopy()
    {
        $this->setBillNumberAndDate();
        $this->getBSK();
    }

}
