<?php

use yii\db\Migration;
use common\models\PaymentsCalculations;

class m161004_120755_calc_profit_for_manager extends Migration
{
    public function up()
    {
        $this->addColumn('{{%payments_calculations}}','profit_for_manager',$this->decimal(19,10));
        $db = Yii::$app->db;
        $sql = 'update wm_payments_calculations c inner join wm_payments p ON c.payment_id = p.id inner join wm_exchange_currency_history e ON p.currency_id = e.currency_id and DATE_FORMAT(FROM_UNIXTIME(p.pay_date),"%Y-%m-%d") = e.date SET c.profit_for_manager = c.profit-(p.pay_summ*e.rate_nbrb*'.PaymentsCalculations::COEF_FOR_PROFIT_MANAGER.')';
        $db->createCommand($sql)->query();
    }

    public function down()
    {
        $this->dropColumn('{{%payments_calculations}}','profit_for_manager');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
