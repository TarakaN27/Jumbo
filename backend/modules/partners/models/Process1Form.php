<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 28.4.16
 * Time: 10.36
 */

namespace backend\modules\partners\models;


use common\models\AbstractActiveRecord;
use common\models\LegalPerson;
use yii\base\Model;
use Yii;

class Process1Form extends Model
{
    public
        $obRequest,
        $amount,
        $currency,
        $legalPerson,
        $contractor,
        $description;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['amount','legalPerson'],'required'],
            ['amount','number'],
            [['currency','legalPerson','contractor'],'integer'],
            ['description','string','max' => 255],
            ['contractor','customValidate','skipOnEmpty' => false, 'skipOnError' => false],
            ['obRequest','safe']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'amount' => Yii::t('app/users','Amount'),
            'legalPerson' => Yii::t('app/users','Legal person'),
            'contractor' => Yii::t('app/users','Contractor')
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function customValidate($attribute,$params)
    {
        if( empty($this->contractor) &&
            LegalPerson::find()->where(['id' => $this->legalPerson,'partner_cntr' => AbstractActiveRecord::YES])->exists())
        {
            $this->addError($attribute,\Yii::t('app/users','Is required'));
        }
    }

    /**
     * 
     */
    public function makeRequest()
    {
        
        
        
        
        
    }




}