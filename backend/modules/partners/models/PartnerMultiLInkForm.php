<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 13.5.16
 * Time: 12.04
 */

namespace backend\modules\partners\models;


use yii\base\Model;

class PartnerMultiLInkForm extends Model
{
    public
        $cntr = NULL,
        $date = null,
        $services = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['cntr'],'required'],
            ['cntr','integer'],
            ['services','each','rule' => ['integer']]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'cntr' => \Yii::t('app/users','Contractor'),
            'services' => \Yii::t('app/users','Services'),
            'date' => \Yii::t('app/users','Date')
        ];
    }

}