<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.4.16
 * Time: 13.01
 */

namespace backend\modules\partners\models;


use common\models\CUser;
use common\models\PartnerCuserServ;
use common\models\Services;
use yii\base\Exception;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use Yii;

class PartnerAllowForm extends Model
{
    public
        $iPartnerID = NULL,
        $obPartner = NULL,
        $oldServices = [],
        $services = [];

    /**
     * @throws NotFoundHttpException
     */
    public function init()
    {
        parent::init();
        $obPartner = CUser::find()->where(['id' => $this->iPartnerID])->partner()->one();
        if(!$obPartner)
            throw new NotFoundHttpException('Partner not found');

        $arAllowServ = $obPartner->partnerAllowServiceIds;
        if($arAllowServ)
            foreach ($arAllowServ as $serv)
                $this->oldServices [] = $serv->service_id;

        $this->services = $this->oldServices;
        $this->obPartner = $obPartner;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['services'],'safe'],
            [['services'],'required'],
            ['services','validateServices']
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateServices($attribute,$params)
    {
        if(!empty($this->oldServices))
        {
            $servDiffIds = array_diff($this->oldServices,$this->services);
            if(PartnerCuserServ::find()->where(['partner_id' => $this->iPartnerID,'service_id' => $servDiffIds])->exists())
            {
                $this->addError($attribute,\Yii::t('app/users','At first you must unlink lead from partner'));
            }
        }
    }

    /**
     * @return array
     */
    public function makeRequest()
    {
        /** @var PartnerCuserServ $obPartner */
        $obPartner = $this->obPartner;

        $tr = Yii::$app->db->beginTransaction();

        try{
            $obPartner->unlinkAll('partnerAllowServices',TRUE);
            if(!empty($this->services))
            {
                $arServices = Services::find()->where(['id' => $this->services])->all();
                foreach ($arServices as $obService)
                    $obPartner->link('partnerAllowServices',$obService);
            }

            $tr->commit();
            return TRUE;
        }catch (Exception $e)
        {
            $tr->rollBack();
            return FALSE;
        }
    }

}