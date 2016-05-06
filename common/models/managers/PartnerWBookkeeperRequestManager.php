<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 6.5.16
 * Time: 12.31
 */

namespace common\models\managers;


use common\models\PartnerWBookkeeperRequest;
use common\models\PartnerExpenseCatLink;
use common\models\Expense;

use common\models\ExchangeCurrencyHistory;
use common\models\PartnerPurse;
use common\models\PartnerPurseHistory;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class PartnerWBookkeeperRequestManager extends PartnerWBookkeeperRequest
{
    /**
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function processPartnerWithdrawal()
    {
        if(null == $iExpenseID = $this->createExpense())
            return FALSE;

        if(!$this->partnerPurseOperations($iExpenseID))
            return FALSE;
        return TRUE;
    }

    /**
     * @return int|null
     */
    protected function createExpense()
    {
        $obCat = PartnerExpenseCatLink::find()->where(['type' => PartnerExpenseCatLink::TYPE_MONEY,'legal_person_id' => $this->legal_id])->one();
        if(!$obCat)
        {
            return NULL;
        }

        $obExpense = new Expense();
        $obExpense->cat_id = $obCat->id;
        $obExpense->currency_id = $this->currency_id;
        $obExpense->cuser_id = $this->contractor_id;
        $obExpense->legal_id = $this->legal_id;
        $obExpense->pay_date = time();
        $obExpense->pay_summ = $this->amount;
        $obExpense->description = $this->description;
        $obExpense->pw_request_id = $this->request_id;

        if($obExpense->save())
            return $obExpense->id;

        return NULL;
    }

    /**
     * @param $iExpenseID
     * @return bool
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    protected function partnerPurseOperations($iExpenseID)
    {
        $pCurr = ExchangeCurrencyHistory::getCurrencyInBURForDate(date('Y-m-d',$this->request->date),$this->currency_id);
        if(!$pCurr)
            throw new NotFoundHttpException('Currency not found');

        $amount = $this->amount*$pCurr;

        $obPurseHistory = new PartnerPurseHistory();
        $obPurseHistory->amount = $amount;
        $obPurseHistory->type = PartnerPurseHistory::TYPE_EXPENSE;
        $obPurseHistory->cuser_id = $this->partner_id;
        $obPurseHistory->expense_id = $iExpenseID;
        if(!$obPurseHistory->save())
            throw new ServerErrorHttpException('Can not save purse history');

        /** @var PartnerPurse $obPurse */
        $obPurse = PartnerPurse::getPurse($this->partner_id);
        $obPurse->withdrawal+=$amount;
        if(!$obPurse->save())
            throw new ServerErrorHttpException('Can not save purse');

        return TRUE;
    }

}