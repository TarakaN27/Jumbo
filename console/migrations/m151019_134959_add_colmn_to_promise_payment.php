<?php

use yii\db\Schema;
use yii\db\Migration;

class m151019_134959_add_colmn_to_promise_payment extends Migration
{
    public function up()
    {
        $this->addColumn('{{%promised_payment}}', 'service_id', Schema::TYPE_INTEGER);
        $this->addForeignKey("pp_fk_service_id", "{{%promised_payment}}", "service_id", "{{%services}}", "id", 'CASCADE','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey("pp_fk_service_id","{{%promised_payment}}");
        $this->dropColumn('{{%promised_payment}}', 'service_id');
    }

}
