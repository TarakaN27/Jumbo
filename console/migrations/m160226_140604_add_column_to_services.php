<?php

use yii\db\Migration;

class m160226_140604_add_column_to_services extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%services}}','not_use_sale',$this->boolean());
        $this->addColumn('{{%services}}','not_use_corr_factor',$this->boolean());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%services}}','not_use_sale');
        $this->dropColumn('{{%services}}','not_use_corr_factor');
    }
}
