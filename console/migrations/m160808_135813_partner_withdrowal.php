<?php

use yii\db\Migration;
use common\models\ExchangeCurrencyHistory;

class m160808_135813_partner_withdrowal extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%partner_purse_history}}','date',$this->integer());
        $sql = 'Update {{%partner_purse_history}} h INNER JOIN {{%payments}} p ON h.payment_id = p.id SET h.date = p.pay_date';
        $this->execute($sql);

        $sql = 'Update {{%enrollment_request}} SET pay_amount = pay_amount/10000 where pay_amount>100000 and pay_currency = 2';
        $this->execute($sql);

        $sql = 'Update {{%enrollment_request}} SET amount = amount/10000 where amount>100000 and service_id = 9';
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
       $this->dropColumn('{{%partner_purse_history}}','date');
    }
}
