<?php

use yii\db\Migration;

class m160223_092341_add_column_to_services extends Migration
{
    public function up()
    {
        $this->addColumn('{{%services}}','allow_enrollment',$this->boolean());
        $this->addColumn('{{%services}}','b_user_enroll',$this->integer());
        $this->addColumn('{{%services}}','enroll_unit',$this->string());

        $this->addForeignKey('FK_srv_b_user_enroll','{{%services}}','b_user_enroll','{{%b_user}}','id','SET NULL','RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('FK_srv_b_user_enroll','{{%services}}');
        $this->dropColumn('{{%services}}','allow_enrollment');
        $this->dropColumn('{{%services}}','b_user_enroll');
        $this->dropColumn('{{%services}}','enroll_unit');
    }
}
