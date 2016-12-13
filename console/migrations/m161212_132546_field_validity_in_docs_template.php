<?php

use yii\db\Migration;
use common\models\BillTemplate;
use common\models\Bills;

class m161212_132546_field_validity_in_docs_template extends Migration
{
    public function up()
    {
        $this->addColumn('{{%bill_template}}','validity', $this->text());
        BillTemplate::updateAll(['validity'=>"Счет действителен в течение двух банковских дней."],['not in','service_id',[3,6,18]]);
    }

    public function down()
    {
        $this->dropColumn('{{%bill_template}}','validity');
        return true;
    }
}
