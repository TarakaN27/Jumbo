<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.4.16
 * Time: 13.58
 */

namespace backend\modules\partners\models;


use yii\base\Model;

class Process3Form extends Model
{
    public
        $arCustomErrors = [],
        $obRequest = NULL,
        $serviceID = NULL,
        $amount = NULL,
        $legalPersonID = NULL,
        $description = NULL;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['serviceID','amount','legalPersonID'],'required'],
            ['description','string','max' => 255],
            ['amount','number'],
            [['serviceID','legalPersonID'],'integer']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'serviceID' => \Yii::t('app/users','Service'),
            'amount' => \Yii::t('app/users','Amount'),
            'legalPersonID' => \Yii::t('app/users','Legal person'),
            'description' => \Yii::t('app/users','Description')
        ];
    }

    /**
     *
     */
    public function makeRequest()
    {
        








    }
}