<?php

namespace common\models;

use DevGroup\TagDependencyHelper\NamingHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%exchange_currency_history}}".
 *
 * @property integer $id
 * @property integer $currency_id
 * @property string $rate_nbrb
 * @property string $old_rate_nbrb
 * @property string $rate_cbr
 * @property string $old_rate_cbr
 * @property integer $user_id
 * @property string $date
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ExchangeRates $currency
 */
class ExchangeCurrencyHistory extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%exchange_currency_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['currency_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['rate_nbrb', 'old_rate_nbrb', 'rate_cbr', 'old_rate_cbr'], 'required'],
            [['rate_nbrb', 'old_rate_nbrb', 'rate_cbr', 'old_rate_cbr'], 'number'],
            [['date'], 'safe'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/services', 'ID'),
            'currency_id' => Yii::t('app/services', 'Currency ID'),
            'rate_nbrb' => Yii::t('app/services', 'Rate Nbrb'),
            'old_rate_nbrb' => Yii::t('app/services', 'Old Rate Nbrb'),
            'rate_cbr' => Yii::t('app/services', 'Rate Cbr'),
            'old_rate_cbr' => Yii::t('app/services', 'Old Rate Cbr'),
            'user_id' => Yii::t('app/services', 'User ID'),
            'date' => Yii::t('app/services', 'Date'),
            'created_at' => Yii::t('app/services', 'Created At'),
            'updated_at' => Yii::t('app/services', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(ExchangeRates::className(), ['id' => 'currency_id']);
    }

    /**
     * Получение курса валюты на указанную дату
     * @param $date
     * @return mixed
     */
    public static function getCurrencyForDate($date,$iCurID)
    {
            return self::find()
                ->where(' date <= :date AND currency_id = :iCurID ')
                ->params([':date' => $date,':iCurID' => $iCurID])
                ->orderBy(['date' => SORT_DESC])
                ->one();
    }

    /**
     * получаем курс валюты в белорусских рублях на дату $date
     * @param $date  
     * @param $iCurID
     * @return float|null
     */
    public static function getCurrencyInBURForDate($date,$iCurID)
    {
        $date = is_numeric($date) ? date('Y-m-d',$date) : $date;
        $returnValue = NULL;                            //возвращаеме значение
        if(date('Y-m-d',time()) == $date)           //если дата равна текущей, то вернем текущее значение курса валюты
        {
            /** @var ExchangeRates $obCurr */
            $obCurr = ExchangeRates::findOneByIDCached($iCurID,FALSE);  //курсы валют текущие
            if(!empty($obCurr))
            {
                $returnValue = (float)$obCurr->nbrb_rate;
            }
        }else{

            //иначе ищем в истории курсов валют
            /** @var ExchangeCurrencyHistory $obECH */
            $obECH = ExchangeCurrencyHistory::getCurrencyForDate($date,$iCurID);    //вытягиеваем курс из истории
            if($obECH)
            {
                $returnValue = (float)$obECH->rate_nbrb;
            }else{
                /** @var ExchangeRates $obCurr */
                $obCurr = ExchangeRates::findOneByIDCached($iCurID,FALSE);  //курсы валют текущие
                if(!empty($obCurr))
                {
                    $returnValue = (float)$obCurr->nbrb_rate;
                }
            }
        }
        return $returnValue;
    }

    /**
     * @param $beginDate
     * @param $endDate
     * @param array $arCurIds
     * @return array
     */
    public static function getCurrencyInByrForPeriod($beginDate,$endDate,array $arCurIds)
    {
        $beginDate = date('Y-m-d',$beginDate);
        $endDate = date('Y-m-d',$endDate);

        $arItems = self::find()
            ->select(['id','currency_id','date','rate_nbrb'])
            ->where(['currency_id' => $arCurIds])
            ->andWhere(['between', 'date', $beginDate, $endDate])
            ->orderBy(['date' => SORT_ASC])
            ->asArray()
            ->all();

        $arItems = ArrayHelper::index($arItems,'date',['currency_id']);

        foreach ($arItems as &$item)
            foreach ($item as &$it)
                $it = (float)$it['rate_nbrb'];

        return $arItems;
    }


    /**
     * @param $beginDate
     * @param $endDate
     * @param array $arCurIds
     * @return array
     */
    public static function getCurrencyInByrForDates($dates,array $arCurIds)
    {
        foreach($dates as $key=>$item){
            $dates[$key] = date('Y-m-d',$item);
        }


        $arItems = self::find()
            ->select(['id','currency_id','date','rate_nbrb'])
            ->where(['currency_id' => $arCurIds])
            ->andWhere(['date'=>$dates])
            ->orderBy(['date' => SORT_ASC])
            ->asArray()
            ->all();

        $arItems = ArrayHelper::index($arItems,'date',['currency_id']);

        foreach ($arItems as &$item)
            foreach ($item as &$it)
                $it = (float)$it['rate_nbrb'];

        return $arItems;
    }

}
