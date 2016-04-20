<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 19.4.16
 * Time: 18.12
 */

namespace common\components\partners;

use common\components\helpers\CustomHelper;
use common\models\CUser;
use common\models\PartnerCuserServ;
use common\models\Payments;
use yii\helpers\ArrayHelper;

class PartnerInactivity
{
    protected
        $leadIdsStartPeriodChecked = [],                    //Lead link which alive
        $leadIdsForArchivate = [];                          //Lead links that should be archived

    /**
     * @return array
     */
    public function checkStartPeriod()
    {
        $arPartners = $this->getPartners();
        if(empty($arPartners))
            return [];
        $arLeads = $this->getPartnerLeadLink($arPartners);
        if(empty($arLeads))
            return [];

        $this->getLeadLinkForArchived($arLeads,$arPartners);
        $this->updatePartnerLeads();
    }

    public function checkRegularPeriod()
    {
        $arPartners = $this->getPartners();
        if(empty($arPartners))
            return [];
        $arLeads = $this->getPartnerLeadLink($arPartners);
        if(empty($arLeads))
            return [];


        $this->getLeadLinkForArchivedReqular($arLeads,$arPartners);

        $this->updatePartnerLeads();
    }

    /**
     *
     */
    public function clearProperty()
    {
        $this->leadIdsForArchivate = [];
        $this->leadIdsStartPeriodChecked = [];
    }


    /**
     * @return mixed
     */
    protected function getPartners()
    {
        return CUser::find()->with('partnerScheme')
            ->select([
                'id',
                'partner_scheme',
                'archive',
                'partner'
            ])
            ->where('archive = 0 OR archive is NULL')
            ->partner()
            ->all();
    }

    /**
     * @param array $arPartners
     * @return array
     */
    protected function getPartnerLeadLink(array $arPartners)
    {
        $arPIds = ArrayHelper::getColumn($arPartners,'id');                 //Partner Ids
        if(empty($arPIds))
            return [];
        return PartnerCuserServ::find()
            ->where(['partner_id' => $arPIds])
            ->andWhere('archive is NULL OR archive = 0')
            ->andWhere('st_period_checked is NULL OR st_period_checked = 0')
            ->all();
    }

    /**
     * @param array $arLead
     * @param array $arPartner
     */
    protected function getLeadLinkForArchived(array $arLead,array $arPartner)
    {
        $arPartnerStartPeriod = ArrayHelper::map($arPartner,'id','partnerScheme.start_period');

        foreach ($arLead as $lead)
        {
            if(!isset($arPartnerStartPeriod[$lead->partner_id]) || is_null($arPartnerStartPeriod[$lead->partner_id]))
                continue;   
            
            $startPeriod = empty($arPartnerStartPeriod[$lead->partner_id]) ? \Yii::$app->config->get('ps_start_period') : (int)$arPartnerStartPeriod[$lead->partner_id];
            $dateConnect = strtotime($lead->connect);
            $endStartPeriod = CustomHelper::getDateMinusNumMonth($dateConnect,$startPeriod,'+');


            if($endStartPeriod > time())
                continue;

            if(Payments::find()
                ->where(['cuser_id' => $lead->cuser_id,'service_id' => $lead->service_id])
                ->andWhere('pay_date >= :connect AND pay_date <= :endStartPeriod')
                ->params([
                    ':connect' => $dateConnect,
                    ':endStartPeriod' => $endStartPeriod
                ])
                ->exist()
            )
            {
                $this->leadIdsStartPeriodChecked[] = $lead->id;

            }else{
                $leadIdsForArchivate[] = $lead->id;
            }
        }
    }

    /**
     * @return bool
     */
    protected function updatePartnerLeads()
    {
        if(count($this->leadIdsForArchivate) > 0)
        {
            $arIds = array_unique($this->leadIdsForArchivate);
            PartnerCuserServ::updateAll(['archive' => PartnerCuserServ::YES],['id' => $arIds]);
        }
        if(count($this->leadIdsStartPeriodChecked) > 0)
        {
            $arIds = array_unique($this->leadIdsStartPeriodChecked);
            PartnerCuserServ::updateAll(['st_period_checked' => PartnerCuserServ::YES],['id' => $arIds]);
        }

        return TRUE;
    }

    /**
     * @param $arLead
     * @param $arPartners
     */
    protected function getLeadLinkForArchivedReqular($arLead,$arPartners)
    {
        $arPartnerRegularPeriod = ArrayHelper::map($arPartners,'id','partnerScheme.regular_period');
        foreach ($arLead as $lead)
        {
            if(!isset($arPartnerRegularPeriod[$lead->partner_id]) || is_null($arPartnerRegularPeriod[$lead->partner_id]))
                continue;

            $regularPeriod = empty($arPartnerRegularPeriod[$lead->partner_id]) ? \Yii::$app->config->get('ps_regular_period') : (int)$arPartnerRegularPeriod[$lead->partner_id];
            $endStartPeriod = CustomHelper::getDateMinusNumMonth(time(),$regularPeriod,'-');

            if(!Payments::find()
                ->where(['cuser_id' => $lead->cuser_id,'service_id' => $lead->service_id])
                ->andWhere('pay_date <= :endStartPeriod')
                ->params([
                    ':endStartPeriod' => $endStartPeriod
                ])
                ->exist()
            )
            {
                $leadIdsForArchivate[] = $lead->id;
            }
        }
    }



}