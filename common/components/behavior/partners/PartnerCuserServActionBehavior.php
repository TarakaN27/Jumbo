<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.4.16
 * Time: 11.13
 */

namespace common\components\behavior\partners;


use common\models\PartnerCuserServ;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use yii\base\Behavior;
use yii\web\ServerErrorHttpException;

class PartnerCuserServActionBehavior extends Behavior
{

    public function events()
    {
        return [
            PartnerCuserServ::EVENT_AFTER_ARCHIVE => 'afterArchive',
            PartnerCuserServ::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }

    /**
     *
     */
    public function afterArchive()
    {
        /** @var PartnerCuserServ $model */
        $model = $this->owner;
        $date = strtotime($model->archiveDate.' 00:00:00');
        if($model->archive == PartnerCuserServ::YES)    //if archive link
        {
            $amount = 0;
            $arHist = PartnerPurseHistory::find()   //find all bonus after action date
                ->alias('pph')
                ->joinWith('payment p')
                ->where('p.pay_date >= :date')
                ->params([':date' => $date])
                ->all();

            $arIds = [];    //get hist ids and amount
            if($arHist)
                foreach ($arHist as $hist) {
                    $amount += $hist->amount;
                    $arIds [] = $hist->id;
                }
            if($arIds)
                PartnerPurseHistory::deleteAll(['id' => $arIds]);   //delete all history

            if($amount > 0) //change purse amount
            {
                /** @var PartnerPurse $obPurse */
                $obPurse = PartnerPurse::find()->where(['cuser_id' => $model->partner_id])->one();
                if($obPurse)
                {
                    $obPurse->amount-=$amount;
                    if(!$obPurse->save())
                        throw new ServerErrorHttpException();
                }
            }
        }else{

            //@todo get bonus after archiveDate
        }

        return TRUE;
    }

    /**
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function afterDelete()
    {
        /** @var PartnerCuserServ $model */
        $model = $this->owner;
        
        $arHist = PartnerPurseHistory::find()
            ->alias('pph')
            ->joinWith('payment p')
            ->where(['p.service_id' => $model->cuser_id,'service_id' => $model->service_id])
            ->all();

        $arIds = [];
        $amount = 0;
        if($arHist)
            foreach ($arHist as $hist)
            {
                $arIds [] = $hist->id;
                $amount+=$hist->amount;
            }

        PartnerPurseHistory::deleteAll(['id' => $arIds]);
        if($amount > 0) //change purse amount
        {
            /** @var PartnerPurse $obPurse */
            $obPurse = PartnerPurse::find()->where(['cuser_id' => $model->partner_id])->one();
            if($obPurse)
            {
                $obPurse->amount-=$amount;
                if(!$obPurse->save())
                    throw new ServerErrorHttpException();
            }
        }

        return TRUE;
    }





}