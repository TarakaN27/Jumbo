<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 5.7.16
 * Time: 15.42
 */

namespace common\components\behavior\bonus;


use common\models\BonusSchemeRecords;
use common\models\BonusSchemeRecordsHistory;
use yii\base\Behavior;

class BonusSchemeRecordBehavior extends Behavior
{
    protected
        $oldParams = [];

    public function events()
    {
        return [
            BonusSchemeRecords::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            BonusSchemeRecords::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    /**
     * 
     */
    public function beforeUpdate()
    {
        /** @var BonusSchemeRecords $model */
        $model = $this->owner;
        $this->oldParams = $model->getOldAttributes();
    }

    /**
     *
     */
    public function afterUpdate()
    {
        if(!empty($this->oldParams))
        {
            $obHistory = new BonusSchemeRecordsHistory($this->oldParams);
            $obHistory->id = '';
            $obHistory->update_date = date('Y-m-d',time());
            $obHistory->save();
        }
    }



}