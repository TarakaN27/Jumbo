<?php

use yii\db\Migration;

class m160301_115816_remove_colmn_ann_add extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%services}}','not_use_sale');
        $this->dropColumn('{{%services}}','not_use_corr_factor');

        $this->addColumn('{{%payment_condition}}','not_use_sale',$this->boolean());
        $this->addColumn('{{%payment_condition}}','not_use_corr_factor',$this->boolean());
    }

    public function safeDown()
    {
        $this->addColumn('{{%services}}','not_use_sale',$this->boolean());
        $this->addColumn('{{%services}}','not_use_corr_factor',$this->boolean());

        $this->dropColumn('{{%payment_condition}}','not_use_sale');
        $this->dropColumn('{{%payment_condition}}','not_use_corr_factor');
    }

}
