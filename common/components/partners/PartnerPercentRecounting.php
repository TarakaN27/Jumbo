<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 23.6.16
 * Time: 13.22
 */

namespace common\components\partners;


use common\components\helpers\CustomHelper;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use common\models\Payments;
use common\models\RecalculatePartner;
use yii\base\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

class PartnerPercentRecounting
{
    protected
        $pId = NULL,                //partner id
        $partnersPurses = [],       //partnersPurses
        $beginMonthTime = NULL,     //begin time for recalculate
        $arRemove = [];             //cuser id for remove from recalculate partner table

    public function dailyRecounting()
    {
        $arPartner = $this->getPartnerAndBeginDate();               //get partner id and date for begin recalculate
        if(!$arPartner)
            return TRUE;

        $tr = \Yii::$app->db->beginTransaction();
        try{
            $arPartnerIds = array_keys($arPartner);
            $this->getPurses($arPartnerIds);
            foreach ($arPartner as $partnerID => $startDate)
            {
                $this->beginMonthTime = CustomHelper::getBeginMonthTime(strtotime($startDate));
                $this->pId = (int)$partnerID;
                $this->purseActions();
                $this->recalculatePartnerPercent();
            }
            $this->clearRecalculatePartner($arPartnerIds);
        }catch (Exception $e){
            $tr->rollBack();
            return FALSE;
        }
        $tr->commit();
        return TRUE;
    }

    /**
     * Get partner and date for begin recalculate
     * @return array
     */
    protected function getPartnerAndBeginDate()
    {
        return ArrayHelper::map((new Query())
            ->select(['cuser_id','begin_date'])
            ->from(RecalculatePartner::tableName())
            ->groupBy(['cuser_id'])
            ->orderBy(['begin_date' => SORT_ASC])
            ->all(),'cuser_id','begin_date');
    }

    /**
     * @param array $arPartnerIds
     * @return int
     */
    protected function clearRecalculatePartner(array $arPartnerIds)
    {
        return RecalculatePartner::deleteAll(['cuser_id' => $arPartnerIds]);
    }

    /**
     * Get purse
     * @return null|static
     */
    protected function getPurse()
    {
        return isset($this->partnersPurses[$this->pId]) ? $this->partnersPurses[$this->pId] : NULL;
    }

    /**
     * @param array $partnerIds
     * @return array
     */
    protected function getPurses(array $partnerIds)
    {
        $tmp = PartnerPurse::find()->where(['cuser_id' => $partnerIds])->all();
        return $this->partnersPurses = ArrayHelper::index($tmp,'cuser_id');
    }

    /**
     * Get partner purse history
     * @return mixed
     */
    protected function getPartnerPurseHistory()
    {
        return PartnerPurseHistory::find()
            ->joinWith('payment')
            ->where([
                PartnerPurseHistory::tableName().'.cuser_id' => $this->pId,
                PartnerPurseHistory::tableName().'.type' => PartnerPurseHistory::TYPE_INCOMING
            ])
            ->andWhere(['>=',Payments::tableName().'.pay_date',$this->beginMonthTime])
            ->all();
    }

    /**
     * Make correcting for partner purse
     * @return bool
     * @throws ServerErrorHttpException
     */
    protected function purseActions()
    {
        /** @var PartnerPurse $obPurse */
        $obPurse = $this->getPurse();
        if(!$obPurse)
            return FALSE;

        $arHistory = $this->getPartnerPurseHistory();
        if(empty($arHistory))
            return FALSE;

        $amount = 0;
        $arHistIds = [];
        /** @var PartnerPurseHistory $history */
        foreach ($arHistory as $history)
        {
            $amount += (float)$history->amount;
            $arHistIds [] = $history->id;
        }

        $obPurse->amount-=$amount;
        if(!$obPurse->save())
            throw new ServerErrorHttpException();

       $arHistIds = array_unique($arHistIds);

        if(!empty($arHistIds))
            if(!PartnerPurseHistory::deleteAll(['id' => $arHistIds]))
                throw new ServerErrorHttpException();

        return TRUE;
    }

    /**
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     */
    protected function recalculatePartnerPercent()
    {
        $obCalc = new PartnerPercentCounting([$this->pId]);
        return $obCalc->countPercentByMonth(CustomHelper::getDateMinusNumMonth($this->beginMonthTime,1,'+'));
    }
}