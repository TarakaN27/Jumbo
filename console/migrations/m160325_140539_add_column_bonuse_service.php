<?php

use yii\db\Migration;

class m160325_140539_add_column_bonuse_service extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bonus_scheme_service}}','simple_percent',$this->money());
    }

    public function down()
    {
        $this->dropColumn('{{%bonus_scheme_service}}', 'simple_percent');
    }
}
